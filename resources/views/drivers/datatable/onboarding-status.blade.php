@if ($onboardingStatus === 'offboarding')
    <span class="badge badge-warning">Offboarding</span>
@elseif ($onboardingStatus === 'onboarding_completed')
    <span class="badge badge-success">Onboarding Completed</span>
@else
    <span class="badge badge-info">Pending Onboarding</span>
@endif
