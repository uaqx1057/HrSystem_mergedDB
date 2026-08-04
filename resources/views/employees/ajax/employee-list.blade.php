@php
    $addEmployeePermission = user()->permission('add_employees');
    $addDesignationPermission = user()->permission('add_designation');
    $viewDesignationPermission = user()->permission('view_designation');
@endphp

<!-- ROW START -->
<div class="row ">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">

        <form action="" id="filter-form">

            <div class="row pb-3">

                <div class="col-md-3 col-sm-6 ">
                    <x-forms.label :fieldLabel="__('app.employee')" fieldId="employee" />
                    <select class="form-control select-picker" name="employee" id="employee" data-live-search="true"
                        data-size="8">
                        @if ($employees->count() > 1 || in_array('admin', user_roles()))
                            <option value="all">@lang('app.all')</option>
                        @endif
                        @foreach ($employees as $employee)
                            <x-user-option :user="$employee" />
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 ">
                    <x-forms.label :fieldLabel="__('app.designation')" fieldId="designation" />
                    <select class="form-control select-picker" name="designation" id="designation"
                        data-live-search="true" data-size="8">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($designations as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 ">
                    <x-forms.label :fieldLabel="__('app.department')" fieldId="department" />
                    <select class="form-control select-picker" name="department" id="department" data-live-search="true"
                        data-size="8">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 ">
                    <x-forms.label :fieldLabel="__('modules.employees.role')" fieldId="role" />
                    <select class="form-control select-picker" name="role" id="role" data-live-search="true"
                        data-size="8">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 ">
                    <x-forms.label :fieldLabel="__('app.status')" fieldId="status" />
                    <select class="form-control select-picker" name="status" id="status" data-live-search="true"
                        data-size="8">
                        <option selected value="all">@lang('app.all')</option>
                        <option value="active">@lang('app.active')</option>
                        <option value="deactive">@lang('app.inactive')</option>
                        <option {{ request('status') == 'ex_employee' ? 'selected' : '' }} value="ex_employee">
                            @lang('modules.employees.exEmployee')</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 ">
                    <x-forms.label :fieldLabel="__('modules.employees.gender')" fieldId="gender" />
                    <select class="form-control select-picker" name="gender" id="gender" data-live-search="true"
                        data-size="8">
                        <option value="all">@lang('app.all')</option>
                        <option value="male">@lang('app.male')</option>
                        <option value="female">@lang('app.female')</option>
                        <option value="others">@lang('app.others')</option>
                    </select>
                </div>

                <!-- SEARCH BY TASK START -->
                <div class="col-md-3 col-sm-6 mt-3 mt-md-0">
                    <x-forms.label fieldId="search-text-field" class="d-none d-lg-block d-md-block " />
                    <div class="input-group bg-grey rounded">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-additional-grey">
                                <i class="fa fa-search f-13 text-dark-grey"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control f-14 p-1 height-35 border" id="search-text-field"
                            placeholder="@lang('app.startTyping')">
                    </div>
                </div>
                <!-- SEARCH BY TASK END -->
                <!-- SEARCH BY TASK END -->

                <!-- RESET START -->
                <div class="col-md-3 col-sm-6 d-flex px-lg-2 px-md-2 px-0 mt-0 mt-lg-4 mt-md-4">

                    <x-forms.button-secondary class="btn-xs d-none height-35" id="reset-filters" icon="times-circle">
                        @lang('app.clearFilters')
                    </x-forms.button-secondary>
                </div>
                <!-- RESET END -->
            </div>
        </form>

        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">

            <div id="table-actions" class="d-block d-lg-flex align-items-center">
                @if (checkCompanyCanAddMoreEmployees(user()->company_id))
                    @if (in_array($addEmployeePermission, ['all', 'added', 'branch']))
                        <x-forms.link-primary :link="route('employees.create')" class="mr-3 openRightModal" icon="plus">
                            @lang('app.addEmployee')
                        </x-forms.link-primary>

                        <x-forms.button-secondary class="mr-3 invite-member mb-2 mb-lg-0" icon="plus">
                            @lang('app.inviteEmployee')
                        </x-forms.button-secondary>
                    @endif

                    @if (in_array($addEmployeePermission, ['all', 'added', 'branch']))
                        <x-forms.link-secondary :link="route('employees.import')"
                            class="mr-3 openRightModal mb-2 mb-lg-0 d-none d-lg-block" icon="file-upload">
                            @lang('app.importExcel')
                        </x-forms.link-secondary>
                    @endif
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="deactive">@lang('app.inactive')</option>
                        <option value="active">@lang('app.active')</option>
                    </select>
                </div>
            </x-datatable.actions>

        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
</div>

@include('sections.datatable_js')

<script>
    var startDate = null;
    var endDate = null;
    var lastStartDate = null;
    var lastEndDate = null;

    @if (request('startDate') != '' && request('endDate') != '')
        startDate = '{{ request('startDate') }}';
        endDate = '{{ request('endDate') }}';
    @endif

    @if (request('lastStartDate') !== '' && request('lastEndDate') !== '')
        lastStartDate = '{{ request('lastStartDate') }}';
        lastEndDate = '{{ request('lastEndDate') }}';
    @endif

    $('#employees-table').on('preXhr.dt', function(e, settings, data) {
        const status = $('#status').val();
        const employee = $('#employee').val();
        const role = $('#role').val();
        const gender = $('#gender').val();
        const skill = $('#skill').val();
        const designation = $('#designation').val();
        const department = $('#department').val();
        const searchText = $('#search-text-field').val();
        data['status'] = status;
        data['employee'] = employee;
        data['role'] = role;
        data['gender'] = gender;
        data['skill'] = skill;
        data['designation'] = designation;
        data['department'] = department;
        data['searchText'] = searchText;

        /* If any of these following filters are applied, then dashboard conditions will not work  */
        if (status == "all" || employee == "all" || role == "all" || designation == "all" || searchText == "") {
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['lastStartDate'] = lastStartDate;
            data['lastEndDate'] = lastEndDate;
        }

    });

    const showTable = () => {
        window.LaravelDataTables["employees-table"].draw(false);
    }

    $('#employee, #status, #role, #gender, #skill, #designation, #department').on('change keyup',
        function() {
            if ($('#status').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else if ($('#employee').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else if ($('#role').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else if ($('#gender').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else if ($('#designation').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else if ($('#department').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }
            showTable();
        });

    $('#search-text-field').on('keyup', function() {
        if ($('#search-text-field').val() != "") {
            $('#reset-filters').removeClass('d-none');
            showTable();
        }
    });

    $('#reset-filters, #reset-filters-2').click(function() {
        $('#filter-form')[0].reset();

        $('#filter-form .select-picker').selectpicker("refresh");
        $('#reset-filters').addClass('d-none');
        showTable();
    });


    $('#quick-action-type').change(function() {
        const actionValue = $(this).val();
        if (actionValue != '') {
            $('#quick-action-apply').removeAttr('disabled');

            if (actionValue == 'change-status') {
                $('.quick-action-field').addClass('d-none');
                $('#change-status-action').removeClass('d-none');
            } else {
                $('.quick-action-field').addClass('d-none');
            }
        } else {
            $('#quick-action-apply').attr('disabled', true);
            $('.quick-action-field').addClass('d-none');
        }
    });

    $('#quick-action-apply').click(function() {
        const actionValue = $('#quick-action-type').val();
        if (actionValue == 'delete') {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    applyQuickAction();
                }
            });

        } else {
            applyQuickAction();
        }
    });

    $('body').on('click', '.terminate-table-row', function() {
        var id = $(this).data('user-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.terminateRecord')",
            input: 'textarea',
            inputPlaceholder: "@lang('app.terminateReasonOptional')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmTerminate')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('employees.terminate-pending', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'POST',
                        'terminate_reason': result.value
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            showTable();
                        }
                    }
                });
            }
        });
    });

    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('user-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('employees.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            showTable();
                        }
                    }
                });
            }
        });
    });

    const applyQuickAction = () => {
        var rowdIds = $("#employees-table input:checkbox:checked").map(function() {
            return $(this).val();
        }).get();

        var url = "{{ route('employees.apply_quick_action') }}?row_ids=" + rowdIds;

        $.easyAjax({
            url: url,
            container: '#quick-action-form',
            type: "POST",
            disableButton: true,
            buttonSelector: "#quick-action-apply",
            data: $('#quick-action-form').serialize(),
            blockUI: true,
            success: function(response) {
                if (response.status == 'success') {
                    showTable();
                    resetActionButtons();
                    deSelectAll();
                    $('#quick-action-form').hide();
                }
            }
        })
    };


    $('body').on('change', '.assign_role', function() {
        var id = $(this).data('user-id');
        var role = $(this).val();
        var token = "{{ csrf_token() }}";

        if (typeof id !== 'undefined') {
            $.easyAjax({
                url: "{{ route('employees.assign_role') }}",
                type: "POST",
                blockUI: true,
                container: '#employees-table',
                data: {
                    role: role,
                    userId: id,
                    _token: token
                },
                success: function(response) {
                    if (response.status == "success") {
                        window.LaravelDataTables["employees-table"].draw(false);
                    }
                }
            })
        }

    });

    $('#designation-setting').click(function() {
        const url = "{{ route('designations.create') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })

    $('.department-setting').click(function() {
        const url = "{{ route('departments.create') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>
