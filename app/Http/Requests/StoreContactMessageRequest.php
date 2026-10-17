<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreContactMessageRequest extends FormRequest
{
    protected $errorBag = 'contact';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('contact_email');

        $this->merge([
            'contact_name' => $this->trimmedString('contact_name'),
            'contact_company' => $this->blankToNull('contact_company'),
            'contact_email' => is_string($email) ? Str::lower(trim($email)) : $email,
            'contact_phone' => $this->blankToNull('contact_phone'),
            'contact_message' => $this->trimmedString('contact_message'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:150'],
            'contact_company' => ['nullable', 'string', 'max:160'],
            'contact_email' => ['required', 'string', 'email:rfc', 'max:254'],
            'contact_phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s]+$/'],
            'contact_message' => ['required', 'string', 'max:2000'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_name.required' => 'Fyll i ditt namn.',
            'contact_name.string' => 'Ange ditt namn som text.',
            'contact_name.max' => 'Namnet får vara högst 150 tecken.',
            'contact_company.string' => 'Ange ett giltigt företagsnamn.',
            'contact_company.max' => 'Företagsnamnet får vara högst 160 tecken.',
            'contact_email.required' => 'Fyll i din e-postadress.',
            'contact_email.string' => 'Ange en giltig e-postadress.',
            'contact_email.email' => 'Ange en giltig e-postadress.',
            'contact_email.max' => 'E-postadressen får vara högst 254 tecken.',
            'contact_phone.string' => 'Ange ett giltigt telefonnummer.',
            'contact_phone.max' => 'Telefonnumret får vara högst 40 tecken.',
            'contact_phone.regex' => 'Ange ett giltigt telefonnummer.',
            'contact_message.required' => 'Skriv ett meddelande.',
            'contact_message.string' => 'Skriv meddelandet som text.',
            'contact_message.max' => 'Meddelandet får vara högst 2000 tecken.',
            'website.prohibited' => 'Formuläret kunde inte skickas.',
        ];
    }

    private function trimmedString(string $key): mixed
    {
        $value = $this->input($key);

        return is_string($value) ? trim($value) : $value;
    }

    private function blankToNull(string $key): mixed
    {
        $value = $this->trimmedString($key);

        if (! is_string($value)) {
            return $value;
        }

        return $value === '' ? null : $value;
    }
}
