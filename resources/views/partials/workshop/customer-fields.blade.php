@php
    $selectedCustomerId = (string) ($selectedCustomerId ?? '');
    $selectedCustomer = $customers->first(fn ($customerOption): bool => (string) $customerOption->getKey() === $selectedCustomerId);
    $searchValue = $selectedCustomer?->name ?? '';
@endphp
<div class="form-field customer-combobox" data-customer-combobox>
    <label for="{{ $prefix }}customer-search">Kund <span class="form-optional">Valfritt</span></label>
    <input id="{{ $prefix }}customer-search" data-customer-search type="search" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="{{ $prefix }}customer-list" autocomplete="off" placeholder="Sök kund" value="{{ $searchValue }}">
    <select id="{{ $prefix }}customer-id" name="customer_id" data-customer-select>
        <option value="" @selected($selectedCustomerId === '')>Ingen kund</option>
        @foreach ($customers as $customerOption)
            <option value="{{ $customerOption->getKey() }}" @selected($selectedCustomerId === (string) $customerOption->getKey())>{{ $customerOption->name }}</option>
        @endforeach
        <option value="new" @selected($selectedCustomerId === 'new')>+ Ny kund</option>
    </select>
    <div id="{{ $prefix }}customer-list" class="customer-combobox__list" data-customer-list role="listbox" hidden></div>
    <div class="customer-combobox__new">
        <div class="form-field">
            <label for="{{ $prefix }}new-customer-name">Namn</label>
            <input id="{{ $prefix }}new-customer-name" name="new_customer_name" type="text" value="{{ is_string(old('new_customer_name')) ? old('new_customer_name') : '' }}" maxlength="160" @if ($errors->has('new_customer_name')) aria-invalid="true" @endif>
            @error('new_customer_name')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-field">
            <label for="{{ $prefix }}new-customer-contact">Kontaktperson</label>
            <input id="{{ $prefix }}new-customer-contact" name="new_customer_contact_name" type="text" value="{{ is_string(old('new_customer_contact_name')) ? old('new_customer_contact_name') : '' }}" maxlength="160">
        </div>
        <div class="form-field">
            <label for="{{ $prefix }}new-customer-phone">Telefon</label>
            <input id="{{ $prefix }}new-customer-phone" name="new_customer_phone" type="tel" value="{{ is_string(old('new_customer_phone')) ? old('new_customer_phone') : '' }}" maxlength="40" autocomplete="tel">
        </div>
        <div class="form-field">
            <label for="{{ $prefix }}new-customer-email">E-post</label>
            <input id="{{ $prefix }}new-customer-email" name="new_customer_email" type="email" value="{{ is_string(old('new_customer_email')) ? old('new_customer_email') : '' }}" maxlength="254" autocomplete="email">
        </div>
        <div class="form-field">
            <label for="{{ $prefix }}new-customer-address">Adress</label>
            <input id="{{ $prefix }}new-customer-address" name="new_customer_address" type="text" value="{{ is_string(old('new_customer_address')) ? old('new_customer_address') : '' }}" maxlength="255">
        </div>
    </div>
    @error('customer_id')
        <p class="form-field__error">{{ $message }}</p>
    @enderror
</div>
