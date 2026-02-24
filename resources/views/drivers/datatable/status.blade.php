@php
    $statusValue = strtolower((string) $status);
@endphp

@if ($statusValue == 'active')
    <i class="fa fa-circle mr-1 text-light-green f-10"></i> {{ __('app.active') }}
@elseif ($statusValue == 'busy')
    <i class="fa fa-circle mr-1 text-warning f-10"></i> Busy
@elseif ($statusValue == 'blocked')
    <i class="fa fa-circle mr-1 text-red f-10"></i> Blocked
@else
    <i class="fa fa-circle mr-1 text-red f-10"></i> {{ __('app.inactive') }}
@endif