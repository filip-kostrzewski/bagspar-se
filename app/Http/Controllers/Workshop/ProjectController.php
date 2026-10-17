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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            'customer_id' => ['nullable'],
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
        $validated = $this->withCustomer($workshop, $request, $validated);
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
            'customer_id' => ['sometimes', 'nullable'],
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

        $validated = $this->withCustomer($workshop, $request, $validated);
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
    private function withCustomer(Workshop $workshop, Request $request, array $validated): array
    {
        $rawChoice = $request->input('customer_id');

        if (is_array($rawChoice)) {
            throw ValidationException::withMessages([
                'customer_id' => 'Välj en kund i listan eller skapa en ny.',
            ]);
        }

        $choice = $request->exists('customer_id') ? trim((string) $rawChoice) : null;
        unset($validated['customer_id']);

        if ($choice === 'new') {
            return $this->withNewCustomer($workshop, $request, $validated);
        }

        if (is_string($choice) && $choice !== '' && ! is_numeric($choice)) {
            throw ValidationException::withMessages([
                'customer_id' => 'Välj en kund i listan eller skapa en ny.',
            ]);
        }

        if (is_numeric($choice)) {
            $customer = $workshop->customers()->whereKey((int) $choice)->first();

            if (! $customer instanceof Customer) {
                abort(404);
            }

            $validated['customer_id'] = $customer->getKey();
            $validated['customer_name'] = $customer->name;

            return $validated;
        }

        $name = isset($validated['customer_name']) && is_string($validated['customer_name'])
            ? trim($validated['customer_name'])
            : '';

        if ($name !== '') {
            $customer = Customer::findOrCreateForWorkshop($workshop, $name);
            $validated['customer_id'] = $customer->getKey();
            $validated['customer_name'] = $customer->name;

            return $validated;
        }

        if ($choice === '' || (array_key_exists('customer_name', $validated) && $name === '')) {
            $validated['customer_id'] = null;
            $validated['customer_name'] = null;
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withNewCustomer(Workshop $workshop, Request $request, array $validated): array
    {
        $email = $request->input('new_customer_email');
        if (is_string($email)) {
            $request->merge(['new_customer_email' => Str::lower(trim($email))]);
        }

        $new = $request->validate([
            'new_customer_name' => ['required', 'string', 'max:160'],
            'new_customer_contact_name' => ['nullable', 'string', 'max:160'],
            'new_customer_phone' => ['nullable', 'string', 'max:40'],
            'new_customer_address' => ['nullable', 'string', 'max:255'],
            'new_customer_email' => ['nullable', 'string', 'email:rfc', 'max:254'],
        ], [
            'new_customer_name.required' => 'Ange kundens namn.',
            'new_customer_name.max' => 'Kundens namn får vara högst 160 tecken.',
            'new_customer_contact_name.max' => 'Kontaktpersonen får vara högst 160 tecken.',
            'new_customer_phone.max' => 'Telefonnumret får vara högst 40 tecken.',
            'new_customer_address.max' => 'Adressen får vara högst 255 tecken.',
            'new_customer_email.email' => 'Ange en giltig e-postadress.',
            'new_customer_email.max' => 'E-postadressen får vara högst 254 tecken.',
        ]);

        $name = trim($new['new_customer_name']);
        $existing = $workshop->customers()->get()->first(
            fn (Customer $customer): bool => mb_strtolower($customer->name) === mb_strtolower($name),
        );

        if ($existing instanceof Customer) {
            throw ValidationException::withMessages([
                'new_customer_name' => 'Kunden finns redan. Välj den i listan.',
            ]);
        }

        $customer = $workshop->customers()->create([
            'name' => mb_substr($name, 0, 160),
            'contact_name' => $this->blankToNull($new['new_customer_contact_name'] ?? null),
            'phone' => $this->blankToNull($new['new_customer_phone'] ?? null),
            'address' => $this->blankToNull($new['new_customer_address'] ?? null),
            'email' => $this->blankToNull($new['new_customer_email'] ?? null),
        ]);
        $validated['customer_id'] = $customer->getKey();
        $validated['customer_name'] = $customer->name;

        return $validated;
    }

    private function blankToNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
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
