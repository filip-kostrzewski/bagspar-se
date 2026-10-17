<div class="form-field">
    <label for="{{ $prefix }}customer-id">Kund i registret <span class="form-optional">Valfritt</span></label>
    <select id="{{ $prefix }}customer-id" name="customer_id">
        <option value="">Ingen vald kund</option>
        @foreach ($customers as $customerOption)
            <option value="{{ $customerOption->getKey() }}" @selected((string) ($selectedCustomerId ?? '') === (string) $customerOption->getKey())>{{ $customerOption->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-field">
    <label for="{{ $prefix }}customer-name">Ny eller befintlig kund <span class="form-optional">Valfritt</span></label>
    <input id="{{ $prefix }}customer-name" name="customer_name" type="text" value="{{ $customerName }}" maxlength="160" @if ($errors->has('customer_name')) aria-invalid="true" @endif>
    <p class="form-field__hint">Samma namn kopplas till den kund som redan finns i verkstaden.</p>
    @error('customer_name')
        <p class="form-field__error">{{ $message }}</p>
    @enderror
</div>
