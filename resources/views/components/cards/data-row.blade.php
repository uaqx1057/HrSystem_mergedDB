@if (!$html)
    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
        <p class="mb-0 {{ $background ? $background : '' }} f-14 w-30 text-capitalize">{{ $label }}</p>
        <p class="mb-0 {{ $background ? $background : '' }}  f-14 w-70 text-wrap">{!! $value !!}</p>
    </div>
@else
    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
        <p class="mb-0 {{ $background ? $background : '' }} f-14 w-30 text-capitalize">{{ $label }}</p>
        <div class="mb-0 {{ $background ? $background : '' }} f-14 w-70 text-wrap ql-editor p-0">{!! nl2br($value) !!}</div>
    </div>
@endif
