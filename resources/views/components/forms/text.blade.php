@props([
    'fieldId',
    'fieldLabel',
    'fieldRequired' => false,
    'popover' => null,
    'fieldPlaceholder' => '',
    'fieldValue' => '',
    'fieldName',
    'fieldReadOnly' => false,
    'fieldHelp' => '',
    'showButton' => false
])

<div {{ $attributes->merge(['class' => 'form-group']) }}>
    <div class="d-flex justify-content-between align-items-center">
        <x-forms.label :fieldId="$fieldId" :fieldLabel="$fieldLabel" :fieldRequired="$fieldRequired" :popover="$popover" class=""></x-forms.label>
    </div>

    <div class="input-group">
        <input type="text"
            class="form-control height-35 f-14"
            placeholder="{{ $fieldPlaceholder }}"
            value="{{ $fieldValue }}"
            name="{{ $fieldName }}"
            id="{{ $fieldId }}"
            @if ($fieldReadOnly == 'true') readonly @endif
        >

        @if ($showButton)
            <button class="btn btn-primary border-grey f-14 ml-1" type="button" onclick="generatePassword('{{ $fieldId }}')">
             Generate
            </button>
        @endif
    </div>

    @if ($fieldHelp)
        <small id="{{ $fieldId }}Help" class="form-text text-muted">{{ $fieldHelp }}</small>
    @endif
</div>

