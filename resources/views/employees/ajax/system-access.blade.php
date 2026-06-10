<div class="row p-20">

    {{-- ── DMS ── --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 f-16">Driver Management System (DMS)</h5>
                @if($systemAccessDms && $systemAccessDms->is_active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-secondary">Inactive</span>
                @endif
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="f-14 text-dark-grey">Role</label>
                    <select id="dms-role" class="form-control select-picker">
                        <option value="">-- Select Role --</option>
                        @foreach($dmsRoles as $id => $name)
                            <option value="{{ $name }}"
                                {{ ($systemAccessDms && $systemAccessDms->role == $name) ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2 mt-3">
                    @if(!$systemAccessDms || !$systemAccessDms->is_active)
                        <button class="btn btn-primary btn-sm" onclick="grantAccess('dms')">
                            <i class="fa fa-check mr-1"></i> Grant Access
                        </button>
                    @else
                        <button class="btn btn-warning btn-sm" onclick="updateRole('dms')">
                            <i class="fa fa-edit mr-1"></i> Update Role
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="revokeAccess('dms')">
                            <i class="fa fa-times mr-1"></i> Revoke Access
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── DOBS ── --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 f-16">Driver Onboarding System (DOBS)</h5>
                @if($systemAccessDobs && $systemAccessDobs->is_active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-secondary">Inactive</span>
                @endif
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="f-14 text-dark-grey">Role</label>
                    <select id="dobs-role" class="form-control select-picker">
                        <option value="">-- Select Role --</option>
                        @foreach($dobsRoles as $role)
                            <option value="{{ $role }}"
                                {{ ($systemAccessDobs && $systemAccessDobs->role == $role) ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2 mt-3">
                    @if(!$systemAccessDobs || !$systemAccessDobs->is_active)
                        <button class="btn btn-primary btn-sm" onclick="grantAccess('dobs')">
                            <i class="fa fa-check mr-1"></i> Grant Access
                        </button>
                    @else
                        <button class="btn btn-warning btn-sm" onclick="updateRole('dobs')">
                            <i class="fa fa-edit mr-1"></i> Update Role
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="revokeAccess('dobs')">
                            <i class="fa fa-times mr-1"></i> Revoke Access
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var employeeId = {{ $employee->id }};

    function grantAccess(system) {
        var role = $('#' + system + '-role').val();
        if (!role) { return showAlert('Please select a role first.'); }

        $.easyAjax({
            url: "{{ route('employees.grant_system_access', ':id') }}".replace(':id', employeeId),
            type: 'POST',
            data: { system: system, role: role, _token: "{{ csrf_token() }}" },
            success: function(r) {
                if (r.status === 'success') { window.location.reload(); }
            }
        });
    }

    function revokeAccess(system) {
        Swal.fire({
            title: 'Revoke access?',
            text: 'This will disable the user in ' + system.toUpperCase(),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, revoke',
            customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false,
            showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
        }).then(function(result) {
            if (result.isConfirmed) {
                $.easyAjax({
                    url: "{{ route('employees.revoke_system_access', ':id') }}".replace(':id', employeeId),
                    type: 'POST',
                    data: { system: system, _token: "{{ csrf_token() }}" },
                    success: function(r) {
                        if (r.status === 'success') { window.location.reload(); }
                    }
                });
            }
        });
    }

    function updateRole(system) {
        var role = $('#' + system + '-role').val();
        if (!role) { return showAlert('Please select a role first.'); }

        $.easyAjax({
            url: "{{ route('employees.update_system_role', ':id') }}".replace(':id', employeeId),
            type: 'POST',
            data: { system: system, role: role, _token: "{{ csrf_token() }}" },
            success: function(r) {
                if (r.status === 'success') { window.location.reload(); }
            }
        });
    }

    function showAlert(msg) {
        Swal.fire({ icon: 'error', text: msg, toast: true, position: 'top-end',
            timer: 3000, timerProgressBar: true, showConfirmButton: false,
            showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' } });
    }
</script>