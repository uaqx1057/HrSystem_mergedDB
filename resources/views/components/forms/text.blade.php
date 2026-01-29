@props([
    'fieldId',
    'fieldLabel',
    'fieldRequired' => false,
    'popover' => '',
    'fieldPlaceholder' => '',
    'fieldValue' => '',
    'fieldName',
    'fieldReadOnly' => false,
    'fieldHelp' => '',
    'showButton' => false
])

<div {{ $attributes->merge(['class' => 'form-group my-3']) }}>
    <div class="d-flex justify-content-between align-items-center">
        <x-forms.label :fieldId="$fieldId" :fieldLabel="$fieldLabel" :fieldRequired="$fieldRequired" :popover="$popover" class="mb-0"></x-forms.label>
        @if ($showButton)
            <button type="button" class="btn btn-primary btn-xs" onclick="generatePassword('{{ $fieldId }}')">Generate Password</button>
        @endif
    </div>

    <input type="text" class="form-control height-35 f-14 mt-2" placeholder="{{ $fieldPlaceholder }}"
        value="{{ $fieldValue }}" name="{{ $fieldName }}" id="{{ $fieldId }}" @if ($fieldReadOnly == 'true') readonly @endif >

    @if ($fieldHelp)
        <small id="{{ $fieldId }}Help" class="form-text text-muted">{{ $fieldHelp }}</small>
    @endif
</div>


