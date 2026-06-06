<!-- HEADER START -->
<header class="main-header clearfix bg-white" id="header">
    @php
        $addSuperadminPermission = user()->permission('add_superadmin');
        $addPackagePermission = user()->permission('add_packages');
        $addCompanyPermission = user()->permission('add_companies');
        $appSettingPermission = user()->permission('manage_superadmin_app_settings');
    @endphp

    <!-- NAVBAR LEFT(MOBILE MENU COLLAPSE) START-->
    <div class="navbar-left float-left d-flex align-items-center">
        <x-app-title class="d-none d-lg-flex" :pageTitle="__($pageTitle)"></x-app-title>

        <div class="d-block d-lg-none menu-collapse cursor-pointer position-relative" onclick="openMobileMenu()">
            <div class="mc-wrap">
                <div class="mcw-line"></div>
                <div class="mcw-line center"></div>
                <div class="mcw-line"></div>
            </div>
        </div>

        @if ($checkListCompleted < $checkListTotal && App::environment('codecanyon'))
            <div class="ml-3 d-none d-lg-block d-md-block">
                <span class="f-12 mb-1"><a href="{{ route('superadmin.checklist') }}" class="text-lightest ">
                        @lang('modules.accountSettings.setupProgress')</a>
                    <span class="float-right">{{ $checkListCompleted }}/{{ $checkListTotal }}</span>
                </span>
                <div class="progress" style="height: 5px; width: 150px">
                    <div class="progress-bar bg-primary" role="progressbar"
                        style="width: {{ ($checkListCompleted / $checkListTotal) * 100 }}%;" aria-valuenow="25"
                        aria-valuemin="0" aria-valuemax="100">&nbsp;
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- NAVBAR LEFT(MOBILE MENU COLLAPSE) END-->
    <!-- NAVBAR RIGHT(SEARCH, ADD, NOTIFICATION, LOGOUT) START-->
    <div class="page-header-right float-right d-flex align-items-center justify-content-end">

        <ul>
            <!-- DARK MODE START -->
            {{-- <li data-toggle="tooltip" data-placement="top" title="Dark/Light Mode" class="d-none d-sm-block">
                <div class="d-flex align-items-center">
                    <input type="checkbox" id="dark-theme-toggle" @if (user()->dark_theme) checked @endif
                        style="display: none !important;">

                    <a href="javascript:;" class="d-block header-icon-box"
                        onclick="document.getElementById('dark-theme-toggle').click();">
                        @if (user()->dark_theme)
                            <i class="fa fa-sun f-16 text-dark-grey"></i> <!-- Dark mood -->
                        @else
                            <i class="fa fa-moon f-16 text-dark-grey"></i> <!-- Light mood -->
                        @endif
                    </a>
                </div>
            </li> --}}
            <!-- DARK MODE END -->
            @if ($appSettingPermission == 'all')
                <!-- Sticky Note START -->
                <li data-toggle="tooltip" data-placement="top" title="{{ __('modules.accountSettings.clearCache') }}"
                    class="d-none d-sm-block cursor-pointer clear-cache">
                    <div class="d-flex align-items-center">
                        <span class="d-block header-icon-box">
                            <i class="fa fa-eraser f-16 text-dark-grey"></i>
                        </span>
                    </div>
                </li>
            @endif
            <!-- Sticky Note START -->
            <li data-toggle="tooltip" data-placement="top" title="{{ __('app.menu.stickyNotes') }}"
                class="d-none d-sm-block">
                <div class="d-flex align-items-center">
                    <a href="{{ route('sticky-notes.index') }}" class="d-block header-icon-box openRightModal">
                        <i class="fa fa-sticky-note f-16 text-dark-grey"></i>
                    </a>
                </div>
            </li>
            <!-- Sticky Note END -->
            <!-- ADD START -->
            @if ($addSuperadminPermission == 'all' || $addPackagePermission == 'all' || $addCompanyPermission == 'all')
                <li data-toggle="tooltip" data-placement="top" title="{{ __('app.createNew') }}">
                    <div class="add_box dropdown">
                        <a class="d-block dropdown-toggle header-icon-box" type="link" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-plus-circle f-16 text-dark-grey"></i>
                        </a>
                        <!-- DROPDOWN - INFORMATION -->
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink"
                            tabindex="0">
                            @if ($addCompanyPermission == 'all')
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                    href="{{ route('superadmin.companies.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('superadmin.addCompany')
                                </a>
                            @endif
                            @if ($addPackagePermission == 'all')
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                    href="{{ route('superadmin.packages.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('superadmin.addPackage')
                                </a>
                            @endif
                            @if ($addSuperadminPermission == 'all')
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                    href="{{ route('superadmin.superadmin.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('superadmin.addSuperAdmin')
                                </a>
                            @endif
                        </div>

                    </div>
                </li>
            @endif
            <!-- NOTIFICATIONS START -->
            <li title="{{ __('app.newNotifications') }}">
                <div class="notification_box dropdown">
                    <a class="d-block dropdown-toggle header-icon-box show-user-notifications" type="link"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bell f-16 text-dark-grey"></i>
                        @if ($unreadNotificationCount > 0)
                            <span
                                class="badge badge-primary unread-notifications-count active-timer-count position-absolute">{{ $unreadNotificationCount }}</span>
                        @endif
                    </a>
                    <!-- DROPDOWN - INFORMATION -->
                    <div class="dropdown-menu dropdown-menu-right notification-dropdown border-0 shadow-lg py-0 bg-additional-grey"
                        tabindex="0">
                        <div
                            class="d-flex px-3 justify-content-between align-items-center border-bottom-grey py-1 bg-white">
                            <div class="___class_+?50___">
                                <p class="f-14 mb-0 text-dark f-w-500">@lang('app.newNotifications')</p>
                            </div>
                            @if ($unreadNotificationCount > 0)
                                <div class="f-12 ">
                                    <a href="javascript:;"
                                        class="text-dark-grey mark-notification-read">@lang('app.markRead')</a> |
                                    <a href="{{ route('all-notifications') }}"
                                        class="text-dark-grey">@lang('app.showAll')</a>
                                </div>
                            @endif
                        </div>
                        <div id="notification-list">

                        </div>

                        @if ($unreadNotificationCount > 6)
                            <div class="d-flex px-3 pb-1 pt-2 justify-content-center bg-additional-grey">
                                <a href="{{ route('all-notifications') }}"
                                    class="text-darkest-grey f-13">@lang('app.showAll')</a>
                            </div>
                        @endif
                    </div>
                </div>
            </li>
            <!-- NOTIFICATIONS END -->
            <!-- LOGOUT START -->
            <li data-toggle="tooltip" data-placement="top" title="{{ __('app.logout') }}">
                <div class="logout_box">
                    <a class="d-block header-icon-box" href="javascript:;"
                        onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
                        <i class="fa fa-power-off f-16 text-dark-grey"></i>
                    </a>
                </div>
            </li>
            <!-- LOGOUT END -->

            <!-- PROFILE DROPDOWN START -->
            <li data-toggle="tooltip" data-placement="top" title="{{ user()->name }}" class="ml-2">
                <div class="profile_box dropdown">

                    <a class="d-flex align-items-center dropdown-toggle header-icon-box" type="link"
                        id="profileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                        <img src="{{ $user->image_url }}" alt="{{ user()->name }}" class="rounded-circle"
                            style="width:20px;height:20px;object-fit:cover;border:2px solid rgba(255,255,255,0.12);">
                    </a>

                    <!-- PROFILE DROPDOWN -->
                    <div class="dropdown-menu dropdown-menu-right sidebar-brand-dropdown ml-3"
                        aria-labelledby="profileDropdown" tabindex="0">

                        <div class="d-flex justify-content-between align-items-center profile-box mx-3">
                            <a
                                @if (in_array('client', user_roles())) href="{{ route('profile-settings.index') }}"
                                @elseif (user()->is_superadmin)
                                    href="{{ route('superadmin.settings.super-admin-profile.index') }}"
                                @else
                                    href="{{ route('employees.show', user()->id) }}" @endif>

                                <div class="profileInfo d-flex align-items-center flex-wrap ">

                                    <div class="profileImg mr-2">
                                        <img class="h-100" style="width: 34px; height: 34px;"
                                            src="{{ $user->image_url }}" alt="{{ user()->name }}">
                                    </div>

                                    <div class="ProfileData">
                                        <h3 class="f-15 f-w-500 text-dark" data-placement="bottom"
                                            data-toggle="tooltip" data-original-title="{{ user()->name }}">

                                            {{ user()->name }}
                                        </h3>

                                        <p class="mb-0 f-12 text-dark-grey">
                                            {{ user()->employeeDetail->designation->name ?? '' }}
                                        </p>
                                    </div>

                                </div>
                            </a>

                            @if (user()->is_superadmin)
                                <a href="{{ route('superadmin.settings.super-admin-profile.index') }}"
                                    data-toggle="tooltip" data-original-title="{{ __('app.menu.profileSettings') }}">

                                    <i class="side-icon bi bi-pencil-square text-dark-grey"></i>
                                </a>
                            @else
                                <a href="{{ route('profile-settings.index') }}" data-toggle="tooltip"
                                    data-original-title="{{ __('app.menu.profileSettings') }}">

                                    <i class="side-icon bi bi-pencil-square text-dark-grey"></i>
                                </a>
                            @endif
                        </div>

                        @if (checkCompanyCanAddMoreEmployees(user()->company_id))
                            @if (
                                !in_array('client', user_roles()) &&
                                    ($sidebarUserPermissions['add_employees'] == 4 || $sidebarUserPermissions['add_employees'] == 1) &&
                                    in_array('employees', user_modules()))
                                <a class="dropdown-item d-flex justify-content-between align-items-center f-15 text-dark invite-member"
                                    href="javascript:;">

                                    <span>@lang('app.inviteMember') {{ $companyName }}</span>

                                    <i class="side-icon bi bi-person-plus"></i>
                                </a>
                            @endif
                        @endif

                        {{-- <a class="dropdown-item d-flex justify-content-between align-items-center f-15 text-dark"
                        href="javascript:;">

                            <label for="dark-theme-toggle">@lang('app.darkTheme')</label>

                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    id="dark-theme-toggle"
                                    @if (user()->dark_theme) checked @endif>

                                <label class="custom-control-label f-14"
                                    for="dark-theme-toggle"></label>
                            </div>
                        </a> --}}

                        <a class="dropdown-item d-flex justify-content-between align-items-center f-15 text-dark"
                            href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">

                            @lang('app.logout')

                            <i class="side-icon bi bi-power"></i>
                        </a>

                        @include('super-admin.sections.choose-company')

                    </div>
                </div>
            </li>
            <!-- PROFILE DROPDOWN END -->
        </ul>
    </div>
    <!-- NAVBAR RIGHT(SEARCH, ADD, NOTIFICATION, LOGOUT) START-->
</header>
<!-- HEADER END -->

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<script>
    $(document).ready(function() {

        $('.show-user-notifications').click(function() {
            const openStatus = $(this).attr('aria-expanded');

            if (typeof openStatus == "undefined" || openStatus == "false") {

                const token = '{{ csrf_token() }}';
                $.easyAjax({
                    type: 'POST',
                    url: "{{ route('show_notifications') }}",
                    container: "#notification-list",
                    blockUI: true,
                    data: {
                        '_token': token
                    },
                    success: function(data) {
                        if (data.status === 'success') {
                            $('#notification-list').html(data.html);
                        }
                    }
                });

            }

        });

        $('.mark-notification-read').click(function() {
            const token = '{{ csrf_token() }}';
            $.easyAjax({
                type: 'POST',
                url: "{{ route('mark_notification_read') }}",
                blockUI: true,
                data: {
                    '_token': token
                },
                success: function(data) {
                    if (data.status === 'success') {
                        $('#notification-list').html('');
                        $('.unread-notifications-count').remove();
                        window.location.reload();
                    }
                }
            });
        });

        $('.clear-cache').click(function() {
            $.easyAjax({
                type: 'GET',
                url: "{{ route('superadmin.superadmin.clear-cache') }}",
                blockUI: true,
                success: function(data) {
                    if (data.status === 'success') {
                        window.location.reload();
                    }
                }
            });
        });

    });
</script>
