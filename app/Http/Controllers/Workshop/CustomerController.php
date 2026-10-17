<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Project;
use App\Support\CustomerDataExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $workshop = $this->currentWorkshop($request);

        return view('app.customers.index', [
            'workshop' => $workshop,
            'customers' => $workshop->customers()->withCount('projects')->orderBy('name')->orderBy('id')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $workshop = $this->currentWorkshop($request);

        return view('app.customers.form', [
            'workshop' => $workshop,
            'customer' => new Customer,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $validated = $this->validated($request);
        $customer = $workshop->customers()->create($validated);

        return redirect()->route('app.customers.edit', $customer)->with('status', 'Sparat');
    }

    public function edit(Request $request, Customer $customer): View
    {
        $workshop = $this->currentWorkshop($request);
        $customer = $workshop->customers()->whereKey($customer->getKey())->firstOrFail();

        return view('app.customers.form', [
            'workshop' => $workshop,
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $customer = $workshop->customers()->whereKey($customer->getKey())->firstOrFail();
        $customer->update($this->validated($request));

        return back()->with('status', 'Sparat');
    }

    public function exportJson(Request $request, Customer $customer): Response
    {
        $customer = $this->customerForWorkshop($request, $customer);
        $json = app(CustomerDataExport::class)->json($customer);

        return response($json, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="kund-'.$customer->getKey().'.json"',
        ]);
    }

    public function exportCsv(Request $request, Customer $customer): StreamedResponse
    {
        $customer = $this->customerForWorkshop($request, $customer);
        $csv = app(CustomerDataExport::class)->csv($customer);

        return response()->streamDownload(function () use ($csv): void {
            echo $csv;
        }, 'kund-'.$customer->getKey().'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $customer = $this->customerForWorkshop($request, $customer);
        Gate::authorize('delete', $customer);

        $validated = $request->validate([
            'removal' => ['required', 'in:anonymize,delete-projects'],
            'confirmation' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) use ($customer): void {
                if (! is_string($value) || ! hash_equals($customer->name, $value)) {
                    $fail('Skriv kundens namn för att bekräfta.');
                }
            }],
        ], [
            'removal.required' => 'Välj om projekten ska behållas eller tas bort.',
            'removal.in' => 'Välj om projekten ska behållas eller tas bort.',
            'confirmation.required' => 'Skriv kundens namn för att bekräfta.',
        ]);

        $projects = $customer->projects()->get();
        $deletesProjects = $validated['removal'] === 'delete-projects';

        if ($deletesProjects) {
            foreach ($projects as $project) {
                $project->deleteStoredPhotoFiles();
            }
        }

        DB::transaction(function () use ($customer, $deletesProjects, $projects): void {
            if ($deletesProjects) {
                foreach ($projects as $project) {
                    if ($project instanceof Project) {
                        $project->delete();
                    }
                }
            } else {
                $customer->projects()->update([
                    'customer_id' => null,
                    'customer_name' => Customer::ANONYMIZED_NAME,
                ]);
            }

            $customer->delete();
        });

        $message = $deletesProjects
            ? 'Kunden, projekten och bildfilerna har tagits bort.'
            : 'Kunden har raderats. Projekten är kvar utan kunduppgifter.';

        return redirect()->route('app.customers.index')->with('status', $message);
    }

    private function customerForWorkshop(Request $request, Customer $customer): Customer
    {
        $workshop = $this->currentWorkshop($request);

        return $workshop->customers()->whereKey($customer->getKey())->firstOrFail();
    }

    /**
     * @return array{name: string, contact_name: ?string, phone: ?string, address: ?string, email: ?string}
     */
    private function validated(Request $request): array
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email:rfc', 'max:254'],
        ], [
            'name.required' => 'Ange kundens namn.',
            'name.max' => 'Kundens namn får vara högst 160 tecken.',
            'contact_name.max' => 'Kontaktpersonen får vara högst 160 tecken.',
            'phone.max' => 'Telefonnumret får vara högst 40 tecken.',
            'address.max' => 'Adressen får vara högst 255 tecken.',
            'email.email' => 'Ange en giltig e-postadress.',
            'email.max' => 'E-postadressen får vara högst 254 tecken.',
        ]);

        foreach (['contact_name', 'phone', 'address', 'email'] as $field) {
            $value = $validated[$field] ?? null;
            $validated[$field] = is_string($value) && trim($value) !== '' ? trim($value) : null;
        }

        $validated['name'] = trim($validated['name']);

        return $validated;
    }
}
