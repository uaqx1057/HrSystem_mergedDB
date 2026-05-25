<!-- SIDEBAR START -->
<aside class="{{ !user()->dark_theme ? 'sidebar-' . $appTheme->sidebar_theme : '' }}">
    <!-- MOBILE CLOSE SIDEBAR PANEL START -->
    <div class="mobile-close-sidebar-panel w-100 h-100" onclick="closeMobileMenu()" id="mobile_close_panel"></div>
    <!-- MOBILE CLOSE SIDEBAR PANEL END -->

    <!-- MAIN SIDEBAR START -->
    <div class="main-sidebar" id="mobile_menu_collapse">
        <!-- SIDEBAR BRAND START -->
        <div class="sidebar-brand-box dropdown cursor-pointer {{ user()->dark_theme ? 'bg-dark' : '' }}">


        </div>
        <!-- SIDEBAR BRAND END -->

        <!-- SIDEBAR MENU START -->
        <div class="sidebar-menu {{ user()->dark_theme ? 'bg-dark' : '' }}" id="sideMenuScroll">
            <!-- WORKSUITESAAS -->
            @if(user()->is_superadmin)
                @include('super-admin.sections.super-admin-menu')
            @else()
                @include('sections.menu')
            @endif
        </div>
        <!-- SIDEBAR MENU END -->
    </div>
    <!-- MAIN SIDEBAR END -->
    <!-- Sidebar Toggler -->
    <div
        class="text-center d-flex justify-content-between align-items-center position-fixed sidebarTogglerBox {{ user()->dark_theme ? 'bg-dark' : '' }}">
        <button class="border-0 d-lg-block d-none text-lightest font-weight-bold" id="sidebarToggle"></button>

        {{-- <div class="d-flex align-items-center">
            @if(isWorksuite() || user()->is_superadmin)
            <p class="mb-0 text-dark-grey px-1 py-0 rounded f-10">v{{ File::get('version.txt') }}</p>
            @endif
            @if(isWorksuiteSaas())
                @if (in_array('admin', user_roles()) )
                    <p class="mb-0"><a href="{{ route('superadmin.faqs.index') }}" class="text-secondary ml-2 f-15" data-toggle="tooltip" data-original-title="{{__('superadmin.contactSupport')}}"><i class="fa fa-question-circle"></i></a></p>
                @elseif(user()->is_superadmin && !global_setting()->frontend_disable)
                    <p class="mb-0"><a target="_blank" data-toggle="tooltip" data-original-title="{{__('superadmin.VisitFrontWebsite')}}" href="{{ route('front.home') }}" class="text-secondary ml-2 f-15"><i class="fa fa-external-link-alt"></i></a></p>
                 @endif
             @endif

        </div> --}}
    </div>
    <!-- Sidebar Toggler -->
</aside>
<!-- SIDEBAR END -->

<script>
    $(document).ready(function() {

        $('.invite-member').click(function() {
            const url = "{{ route('employees.invite_member') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#dark-theme-toggle').change(function() {
            const darkTheme = ($(this).is(':checked')) ? '1' : '0'

            $.easyAjax({
                type: 'POST',
                url: "{{ route('profile.dark_theme') }}",
                blockUI: true,
                data: {
                    '_token': '{{ csrf_token() }}',
                    'darkTheme': darkTheme
                },
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.reload();
                    }
                }
            });

        });

    });
</script>
