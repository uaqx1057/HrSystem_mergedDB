@extends('layouts.app')
@section('content')<div class="content-wrapper"><div class="card"><div class="card-body"><h4>IT access-revocation worklist</h4>@forelse($tasks as $task)<div class="border-bottom py-2">{{ $task->employee_name }} — {{ $task->title }}</div>@empty<p>No pending access-revocation tasks.</p>@endforelse</div></div></div>@endsection
