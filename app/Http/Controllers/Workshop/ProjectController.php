<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Project;
use App\Models\SashPhoto;
use App\Models\Workshop;
use App\Support\ReadyCustomerNotifier;
use App\Support\SashQrCodeRenderer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function create(Request $request): View
    {
        $workshop = $this->currentWorkshop($request);

        return view('app.projects.create', [
            'workshop' => $workshop,
            'customers' => $workshop->customers()->orderBy('name')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'customer_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'name.required' => 'Ange ett projektnamn.',
            'name.string' => 'Ange projektnamnet som text.',
            'name.max' => 'Projektnamnet får vara högst 160 tecken.',
            'customer_name.string' => 'Ange kundnamnet som text.',
            'customer_name.max' => 'Kundnamnet får vara högst 160 tecken.',
            'location.string' => 'Ange platsen som text.',
            'location.max' => 'Platsen får vara högst 255 tecken.',
            'notes.string' => 'Ange anteckningarna som text.',
            'notes.max' => 'Anteckningarna får vara högst 5 000 tecken.',
        ]);

        $validated['notify_customer_when_ready'] = $request->boolean('notify_customer_when_ready');
        $validated = $this->withCustomer($workshop, $validated);
        $project = $workshop->projects()->create($validated);

        return redirect()->route('app.projects.show', $project)->with('status', 'Projektet har sparats.');
    }

    public function show(Request $request, Project $project): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sashes = $project->windowSashes()->orderBy('code')->get();
        $customers = $workshop->customers()->orderBy('name')->orderBy('id')->get();
        $project->load('customer');

        return view('app.projects.show', compact('workshop', 'project', 'sashes', 'customers'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'customer_id' => ['sometimes', 'nullable', 'integer'],
            'customer_name' => ['sometimes', 'nullable', 'string', 'max:160'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'required', Rule::in(['active', 'completed'])],
        ], [
            'name.required' => 'Ange ett projektnamn.',
            'name.string' => 'Ange projektnamnet som text.',
            'name.max' => 'Projektnamnet får vara högst 160 tecken.',
            'customer_name.string' => 'Ange kundnamnet som text.',
            'customer_name.max' => 'Kundnamnet får vara högst 160 tecken.',
            'location.string' => 'Ange platsen som text.',
            'location.max' => 'Platsen får vara högst 255 tecken.',
            'notes.string' => 'Ange anteckningarna som text.',
            'notes.max' => 'Anteckningarna får vara högst 5 000 tecken.',
            'status.in' => 'Välj en giltig projektstatus.',
        ]);

        abort_if($validated === [], 422);

        if ($request->input('form_context') === 'project') {
            $validated['notify_customer_when_ready'] = $request->boolean('notify_customer_when_ready');
        }

        $validated = $this->withCustomer($workshop, $validated);
        $project->update($validated);
        $sent = app(ReadyCustomerNotifier::class)->sync($project);
        $message = $sent
            ? 'Projektet har uppdaterats. Kunden har fått ett mejl om att alla bågar är klara.'
            : 'Projektet har uppdaterats.';

        return back()->with('status', $message);
    }

    public function report(Request $request, Project $project): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $project->load('customer');
        $sashes = $project->windowSashes()
            ->with(['photos' => fn (HasMany $query) => $query->inDocumentOrder()])
            ->orderBy('code')
            ->get();
        $clientStatusUrl = $project->clientStatusIsPublic()
            ? route('client-status.show', ['token' => $project->client_status_token])
            : null;
        $clientStatusQr = null;

        if (is_string($clientStatusUrl)) {
            $clientStatusQr = preg_replace(
                '/^<\?xml[^>]*>\s*/',
                '',
                app(SashQrCodeRenderer::class)->render($clientStatusUrl),
            );
        }

        $includeWorkNotes = $request->boolean('anteckningar');

        return view('app.projects.report', compact(
            'workshop',
            'project',
            'sashes',
            'clientStatusUrl',
            'clientStatusQr',
            'includeWorkNotes',
        ));
    }

    public function archive(Request $request, Project $project): RedirectResponse
    {
        $project = $this->ownedProject($request, $project);
        $action = $request->string('action')->toString();
        abort_unless(in_array($action, ['archive', 'restore'], true), 422);

        $project->archived_at = $action === 'archive' ? now() : null;
        $project->save();

        return redirect()
            ->route('app.dashboard', $action === 'restore' ? [] : ['arkiv' => 1])
            ->with('status', $action === 'archive' ? 'Projektet har arkiverats.' : 'Projektet har återställts.');
    }

    public function confirmDestroy(Request $request, Project $project): View
    {
        $project = $this->ownedProject($request, $project);

        return view('app.projects.confirm-destroy', [
            'workshop' => $this->currentWorkshop($request),
            'project' => $project,
        ]);
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $project = $this->ownedProject($request, $project);
        $confirmed = $request->string('confirmation')->toString();
        abort_unless($confirmed === 'ta-bort', 422);

        $paths = SashPhoto::query()
            ->whereIn('sash_id', $project->windowSashes()->select('window_sashes.id'))
            ->pluck('path');

        foreach ($paths as $path) {
            $this->deleteStoredSashPhoto($path);
        }

        $project->delete();

        return redirect()->route('app.dashboard')->with('status', 'Projektet och bildfilerna har tagits bort.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withCustomer(Workshop $workshop, array $validated): array
    {
        $name = isset($validated['customer_name']) && is_string($validated['customer_name'])
            ? trim($validated['customer_name'])
            : '';
        $customerId = $validated['customer_id'] ?? null;
        unset($validated['customer_id']);

        if ($name !== '') {
            $customer = Customer::findOrCreateForWorkshop($workshop, $name);
            $validated['customer_id'] = $customer->getKey();
            $validated['customer_name'] = $customer->name;

            return $validated;
        }

        if (array_key_exists('customer_name', $validated) && ! is_numeric($customerId)) {
            $validated['customer_id'] = null;
            $validated['customer_name'] = null;

            return $validated;
        }

        if (is_numeric($customerId)) {
            $customer = $workshop->customers()->whereKey((int) $customerId)->first();

            if (! $customer instanceof Customer) {
                abort(404);
            }

            $validated['customer_id'] = $customer->getKey();
            $validated['customer_name'] = $customer->name;
        }

        return $validated;
    }

    private function ownedProject(Request $request, Project $project): Project
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        Gate::authorize('delete', $project);

        return $project;
    }

    private function deleteStoredSashPhoto(mixed $path): void
    {
        if (! is_string($path) || ! str_starts_with($path, 'sash-photos/') || str_contains($path, '..')) {
            return;
        }

        Storage::disk('local')->delete($path);
    }
}
