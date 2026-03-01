@php
    use App\Models\Leave;

    $active = false;

    if (isset($user?->session)) {
        $lastSeen = \Carbon\Carbon::createFromTimestamp($user?->session->last_activity)->timezone(company()?company()->timezone:$user?->company->timezone);

        $lastSeenDifference = now()->diffInSeconds($lastSeen);
        if ($lastSeenDifference > 0 && $lastSeenDifference <= 90) {
            $active = true;
        }
    }

    // ── MILESTONE BADGES ─────────────────────────────────────────────────────
    $milestoneBadgesHtml = '';

    $joiningDate = ($user?->employeeDetail && !is_null($user?->employeeDetail->joining_date))
        ? \Carbon\Carbon::parse($user?->employeeDetail->joining_date)
        : null;

    $fullLeaves = Leave::where('user_id', $user?->id)
    ->where('status', 'approved')->whereNull('half_day_type')
    ->whereMonth('leave_date', now()->month)
    ->whereYear('leave_date', now()->year)
    ->count();

    $halfLeaves = Leave::where('user_id', $user?->id)
    ->where('status', 'approved')->whereNotNull('half_day_type')
    ->whereMonth('leave_date', now()->month)
    ->whereYear('leave_date', now()->year)
    ->count();

    $half = $halfLeaves / 2;

    $totalLeave = $fullLeaves + $half;

    $showLeave = 2.5 - $totalLeave;

    if($totalLeave == 0){
        // Badge 1: 2.5 Days Paid Leave — awarded for every completed year
            $milestoneBadgesHtml .= '<span class="ml-2 badge badge-success pr-1"
                data-toggle="tooltip"
                data-original-title="2.5 days paid leave awarded">2.5 Days Paid Leave</span>';
    } elseif($totalLeave > 2.5){
            $milestoneBadgesHtml .= '<span class="ml-2 badge badge-success pr-1"
                data-toggle="tooltip"
                data-original-title="2.5 days paid leave awarded">0 Days Paid Leave</span>';
    } else{
        $milestoneBadgesHtml .= '<span class="ml-2 badge badge-success pr-1"
                data-toggle="tooltip"
                data-original-title="2.5 days paid leave awarded">'.$showLeave.' Days Paid Leave</span>';
    }

    if ($joiningDate) {
        $yearsCompleted = (int) $joiningDate->diffInYears(now(company() ? company()->timezone : $user?->company->timezone));

        if ($yearsCompleted >= 1) {
            // Badge 2: Homeland — awarded at 1 year completion
            $milestoneBadgesHtml .= '<span class="ml-1 badge badge-primary pr-1"
                data-toggle="tooltip"
                data-original-title="Completed ' . $yearsCompleted . ' year(s) — Homeland badge awarded">
                ticket for homeland
            </span>';
        }
    }
    // ─────────────────────────────────────────────────────────────────────────
@endphp

<div class="media align-items-center mw-250">

    @if (!is_null($user))
        <a href="{{ isset($disabledLink) ? 'javascript:;' : route('employees.show', [$user?->id]) }}"
           class="position-relative {{ isset($disabledLink) ? 'disabled-link' : '' }}">
            @if ($active)
                <span class="text-light-green position-absolute f-8 user-online"
                      title="@lang('modules.client.online')"><i class="fa fa-circle"></i></span>
            @endif
            <img src="{{ $user?->image_url }}" class="mr-2 taskEmployeeImg rounded-circle"
                 alt="{{ $user?->name }}" title="{{ $user?->userBadge() }}">
        </a>
        <div class="media-body {{$user?->status}}">

            <h5 class="mb-0 f-12">
                <a href="{{  isset($disabledLink) ? 'javascript:;' : route('employees.show', [$user?->id]) }}"
                   class="text-darkest-grey {{ isset($disabledLink) ? 'disabled-link' : '' }}">{!! $user?->userBadge() !!}</a>
            </h5>
            <p class="mb-0 f-12 text-dark-grey">
                {{ $user?->employeeDetail && !is_null($user?->employeeDetail->designation) ? $user?->employeeDetail->designation->name : ' ' }}
            </p>
        </div>
    @else
        --
    @endif
</div>
{{-- @if (isset($leave))
    {!! $milestoneBadgesHtml !!}
@endif --}}