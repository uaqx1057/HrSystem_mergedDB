@if ($status == 'active')
    <i class="fa fa-circle mr-1 text-light-green f-10"></i> {{  __('app.active') }}
@else
<i class="fa fa-circle mr-1 text-red f-10"></i> {{ __('app.inactive') }}
@endif