<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

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

        return redirect()->route('app.customers.edit', $customer)->with('status', 'Kunden har sparats.');
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

        return back()->with('status', 'Kunden har sparats.');
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
