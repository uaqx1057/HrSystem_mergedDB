@extends('layouts.app')
@push('styles')<link rel="stylesheet" href="{{ asset('vendor/full-calendar/main.min.css') }}">@endpush
@section('content')
<div class="content-wrapper"><div class="d-flex justify-content-between mb-3"><h4>Team leave calendar</h4><a href="{{ route('hr-leave-delegations.index') }}" class="btn btn-light">Delegations</a></div><div class="card"><div class="card-body"><div id="calendar"></div></div></div></div>
@endsection
@push('scripts')
<script src="{{ asset('vendor/full-calendar/main.min.js') }}"></script>
<script>new FullCalendar.Calendar(document.getElementById('calendar'), {initialView:'dayGridMonth', headerToolbar:{left:'prev,next today',center:'title',right:'dayGridMonth,listMonth'}, events:'{{ route('hr-leave-delegations.team-calendar') }}'}).render();</script>
@endpush
