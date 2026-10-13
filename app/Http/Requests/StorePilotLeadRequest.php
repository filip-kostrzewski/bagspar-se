<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePilotLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:160'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'message' => ['nullable', 'string', 'max:2000'],
            'contact_consent' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company.required' => 'Fyll i företag eller verkstad.',
            'company.string' => 'Ange ett giltigt företagsnamn.',
            'company.max' => 'Företagsnamnet får vara högst 160 tecken.',
            'email.required' => 'Fyll i din e-postadress.',
            'email.string' => 'Ange en giltig e-postadress.',
            'email.email' => 'Ange en giltig e-postadress.',
            'email.max' => 'E-postadressen får vara högst 254 tecken.',
            'message.string' => 'Ange text i meddelandefältet.',
            'message.max' => 'Texten får vara högst 2 000 tecken.',
            'contact_consent.accepted' => 'Du behöver godkänna att vi kontaktar dig om Bågspår.',
            'website.prohibited' => 'Formuläret kunde inte skickas.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'company' => 'företag eller verkstad',
            'email' => 'e-postadress',
            'message' => 'meddelande',
            'contact_consent' => 'samtycke',
        ];
    }
}
