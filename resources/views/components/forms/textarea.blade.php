<div {{ $attributes->merge(['class' => 'form-group']) }}>
    <x-forms.label :fieldId="$fieldId" :fieldLabel="$fieldLabel" :fieldRequired="$fieldRequired" :popover="$popover"></x-forms.label>

    <textarea class="form-control f-14 pt-2" rows="3" placeholder="{{ $fieldPlaceholder }}" name="{{ $fieldName }}"
        id="{{ $fieldId }}">{{ $fieldValue }}</textarea>
</div>
