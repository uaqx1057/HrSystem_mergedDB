@push('styles')
    @foreach ($frontWidgets as $item)
        @if(!is_null($item->header_script))
            {!! $item->header_script !!}
        @endif
    @endforeach
    <style>
        .login_section {
            position: relative;
            min-height: 90vh;
            display: flex;
            align-items: center;
            /* CHANGE 1: Set the main background to your purple color */
            background: #364574 !important;
            overflow: hidden;
        }

        .login_section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("{{ asset('img/login-image/car-main.png') }}") !important;
            background-position: center center;
            background-size: cover;
            background-repeat: no-repeat;

            /* CHANGE 2: Adjust opacity (0.1 looks good on dark colors) */
            opacity: 0.15;

            /* CHANGE 3: Blend mode makes the image merge with the purple */
            background-blend-mode: overlay;

            z-index: 0;
        }

        .login_section .container {
            position: relative;
            z-index: 1;
        }

        body{
            background-color: #364574 !important;
        }

        .ms-steps-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 0 0 24px;
            flex-wrap: wrap;
        }
        .ms-step {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .ms-step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #dee2e6;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: #adb5bd;
            transition: all .25s;
            flex-shrink: 0;
        }
        .ms-step-label {
            font-size: 11px;
            color: #adb5bd;
            font-weight: 500;
            white-space: nowrap;
            transition: color .25s;
        }
        .ms-step.active .ms-step-circle { border-color: #722C81; background: #722C81; color: #fff; }
        .ms-step.active .ms-step-label  { color: #722C81; }
        .ms-step.done   .ms-step-circle { border-color: #722C81; background: #722C81; color: #fff; }
        .ms-step.done   .ms-step-label  { color: #722C81; }
        .ms-step-line {
            height: 2px;
            width: 28px;
            background: #dee2e6;
            flex-shrink: 0;
            transition: background .25s;
        }
        .ms-step-line.done { background: #722C81; }

        .inv-step { display: none; }
        .inv-step.active { display: block; }

        .inv-nav { display: flex; justify-content: space-between; margin-top: 20px; gap: 10px; }
        .inv-nav .btn { flex: 1; height: 46px; font-size: 15px; font-weight: 500; }

        .inv-field label { font-size: 13px; font-weight: 500; color: #555; margin-bottom: 4px; display: block; }
        .inv-field { margin-bottom: 16px; }
        .inv-field input, .inv-field select {
            width: 100%;
            height: 46px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 0 12px;
            font-size: 14px;
            color: #333;
            background: #fff;
            outline: none;
            transition: border-color .2s;
        }
        .inv-field input:focus, .inv-field select:focus { border-color: #722C81; }
        .inv-field input[type="file"] { height: auto; padding: 8px 12px; }
        .inv-review-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .inv-review-box .inv-review-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .inv-review-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #eee; }
        .inv-review-row:last-child { border-bottom: none; }
        .inv-review-row span:first-child { color: #888; }
        .inv-review-row span:last-child  { font-weight: 500; color: #333; max-width: 60%; text-align: right; word-break: break-all; }
        .inv-notice {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 13px;
            color: #795548;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .inv-notice i { margin-top: 2px; color: #f59f00; flex-shrink: 0; }

        .login_box{
            width: 550px !important;
        }
    </style>
@endpush

<x-auth>
    @if($isAllowedInCurrentPackage)
        <x-form id="acceptInviteForm" enctype="multipart/form-data">
            <input type="hidden" name="send_mail_to_admin" value="yes">

            <h3 class="text-capitalize mb-3 f-w-500">@lang('app.signUp')</h3>

            <div class="alert alert-danger m-t-10 d-none"  id="alert"></div>
            {{-- <div class="alert alert-success m-t-10 d-none" id="success-msg"></div> --}}

            {{-- ── STEP INDICATORS ── --}}
            <div class="ms-steps-wrapper" id="ms-steps-wrapper">
                <div class="ms-step active" data-step="1">
                    <div class="ms-step-circle">1</div>
                    <span class="ms-step-label">Personal</span>
                </div>
                <div class="ms-step-line" id="line-1"></div>
                <div class="ms-step" data-step="2">
                    <div class="ms-step-circle">2</div>
                    <span class="ms-step-label">Account</span>
                </div>
                <div class="ms-step-line" id="line-2"></div>
                <div class="ms-step" data-step="3">
                    <div class="ms-step-circle">3</div>
                    <span class="ms-step-label">Documents</span>
                </div>
                <div class="ms-step-line" id="line-3"></div>
                <div class="ms-step" data-step="4">
                    <div class="ms-step-circle">4</div>
                    <span class="ms-step-label">Review</span>
                </div>
            </div>

            {{-- ══════════════════════════════
                 STEP 1 — Personal Info
            ══════════════════════════════ --}}
            <div class="inv-step active" id="inv-step-1">

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.client.salutation')</label>
                    <select name="salutation" id="salutation">
                        <option value="">-- Select --</option>
                        @foreach ($salutations as $salutation)
                            <option value="{{ $salutation->value }}">{{ $salutation->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.fullName') <sup>*</sup></label>
                    <input type="text" name="name" id="user-name"
                           placeholder="@lang('placeholders.name')">
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.gender')</label>
                    <select name="gender" id="gender">
                        <option value="">-- Select --</option>
                        <option value="male">@lang('app.male')</option>
                        <option value="female">@lang('app.female')</option>
                        <option value="others">@lang('app.others')</option>
                    </select>
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.dateOfBirth')</label>
                    <input type="date" name="date_of_birth" id="date_of_birth">
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('app.country')</label>
                    <select name="country" id="country">
                        <option value="">-- Select Country --</option>
                        @foreach ($countries as $item)
                            <option value="{{ $item->id }}"
                                    data-phonecode="{{ $item->phonecode }}">
                                {{ $item->nicename }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('app.mobile')</label>
                    <div style="display:flex; gap:8px;">
                        <select name="country_phonecode" id="country_phonecode"
                                style="width:110px; flex-shrink:0; height:46px; border:1px solid #dee2e6; border-radius:6px; padding:0 8px; font-size:14px;">
                            @foreach ($countries as $item)
                                <option value="{{ $item->phonecode }}">+{{ $item->phonecode }}</option>
                            @endforeach
                        </select>
                        <input type="tel" name="mobile" id="mobile"
                               placeholder="@lang('placeholders.mobile')"
                               style="flex:1;">
                    </div>
                </div>

                <div class="inv-nav">
                    <button type="button" class="btn btn-primary inv-next-btn" data-next="2">
                        @lang('app.next') &nbsp;<i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- ══════════════════════════════
                 STEP 2 — Account
            ══════════════════════════════ --}}
            <div class="inv-step" id="inv-step-2">

                @if (!is_null($invite->email_restriction))
                    <div class="inv-field">
                        <label class="text-left" >@lang('app.email') <sup>*</sup></label>
                        <div style="display:flex; align-items:center; gap:0;">
                            <input type="text" name="email_address" id="email_address"
                                   placeholder="username"
                                   style="border-radius:6px 0 0 6px; border-right:none;">
                            <span style="height:46px; padding:0 12px; border:1px solid #dee2e6; border-radius:0 6px 6px 0; display:flex; align-items:center; font-size:14px; background:#f8f9fa; color:#555; white-space:nowrap;">
                                @{{ '@'.$invite->email_restriction }}
                            </span>
                        </div>
                        <input type="hidden" name="email_domain" id="email_domain"
                               value="{{ $invite->email_restriction }}">
                        <input type="hidden" name="email" id="user-email">
                    </div>
                @else
                    <div class="inv-field">
                        <label class="text-left" >@lang('app.email') <sup>*</sup></label>
                        <input type="email" name="email" id="user-email"
                               placeholder="@lang('placeholders.email')">
                    </div>
                @endif

                <div class="inv-field">
                    <label class="text-left" >@lang('app.password') <sup>*</sup></label>
                    <input type="password" name="password" id="password"
                           placeholder="@lang('placeholders.password')">
                </div>

                @if ($globalSetting->sign_up_terms == 'yes')
                    <div class="inv-field" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" id="read_agreement" name="terms_and_conditions"
                               style="width:auto; height:auto;">
                        <label class="text-left"  for="read_agreement" style="margin:0; font-size:13px;">
                            @lang('app.acceptTerms')
                            <a href="{{ $globalSetting->terms_link }}" target="_blank">@lang('app.termsAndCondition')</a>
                        </label>
                    </div>
                @endif

                <div class="inv-nav">
                    <button type="button" class="btn btn-secondary inv-prev-btn" data-prev="1">
                        <i class="fa fa-arrow-left"></i> &nbsp;@lang('app.previous')
                    </button>
                    <button type="button" class="btn btn-primary inv-next-btn" data-next="3">
                        @lang('app.next') &nbsp;<i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- ══════════════════════════════
                 STEP 3 — Documents
            ══════════════════════════════ --}}
            <div class="inv-step" id="inv-step-3">

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.Iqama No') <sup>*</sup></label>
                    <input type="text" name="iqama_no" id="iqama_no"
                           placeholder="@lang('placeholders.iqama')">
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.iqama_profession') <sup>*</sup></label>
                    <input type="text" name="iqama_profession" id="iqama_profession"
                           placeholder="@lang('placeholders.iqama_profession')">
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.iqama_expiry_date') <sup>*</sup></label>
                    <input type="date" name="iqama_expiry_date" id="iqama_expiry_date">
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.iqama_image')</label>
                    <input type="file" name="iqama_image" id="iqama_image"
                           accept=".png,.jpg,.jpeg,.svg,.bmp">
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.passport_no') <sup>*</sup></label>
                    <input type="text" name="passport_no" id="passport_no"
                           placeholder="@lang('placeholders.passport_no')">
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.passport_expiry_date') <sup>*</sup></label>
                    <input type="date" name="passport_expiry_date" id="passport_expiry_date">
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.passport_image')</label>
                    <input type="file" name="passport_image" id="passport_image"
                           accept=".png,.jpg,.jpeg,.svg,.bmp">
                </div>

                <div class="inv-field">
                    <label class="text-left" >@lang('modules.employees.Sponsor / kafala') <sup>*</sup></label>
                    <input type="text" name="sponsor_kafala" id="sponsor_kafala"
                           placeholder="@lang('placeholders.sponsor_kafala')">
                </div>

                <div class="inv-nav">
                    <button type="button" class="btn btn-secondary inv-prev-btn" data-prev="2">
                        <i class="fa fa-arrow-left"></i> &nbsp;@lang('app.previous')
                    </button>
                    <button type="button" class="btn btn-primary inv-next-btn" data-next="4">
                        @lang('app.next') &nbsp;<i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- ══════════════════════════════
                 STEP 4 — Review & Submit
            ══════════════════════════════ --}}
            <div class="inv-step" id="inv-step-4">

                <div class="inv-notice">
                    <i class="fa fa-info-circle"></i>
                    <span>Please review your information before submitting. After signup, HR will verify your details and activate your account.</span>
                </div>

                {{-- Personal summary --}}
                <div class="inv-review-box">
                    <div class="inv-review-title">Personal Info</div>
                    <div class="inv-review-row">
                        <span>Full Name</span>
                        <span id="rev-name">—</span>
                    </div>
                    <div class="inv-review-row">
                        <span>Gender</span>
                        <span id="rev-gender">—</span>
                    </div>
                    <div class="inv-review-row">
                        <span>Date of Birth</span>
                        <span id="rev-dob">—</span>
                    </div>
                    <div class="inv-review-row">
                        <span>Mobile</span>
                        <span id="rev-mobile">—</span>
                    </div>
                </div>

                {{-- Account summary --}}
                <div class="inv-review-box">
                    <div class="inv-review-title">Account</div>
                    <div class="inv-review-row">
                        <span>Email</span>
                        <span id="rev-email">—</span>
                    </div>
                </div>

                {{-- Documents summary --}}
                <div class="inv-review-box">
                    <div class="inv-review-title">Documents</div>
                    <div class="inv-review-row">
                        <span>Iqama No</span>
                        <span id="rev-iqama-no">—</span>
                    </div>
                    <div class="inv-review-row">
                        <span>Iqama Profession</span>
                        <span id="rev-iqama-prof">—</span>
                    </div>
                    <div class="inv-review-row">
                        <span>Iqama Expiry</span>
                        <span id="rev-iqama-exp">—</span>
                    </div>
                    <div class="inv-review-row">
                        <span>Passport No</span>
                        <span id="rev-passport-no">—</span>
                    </div>
                    <div class="inv-review-row">
                        <span>Passport Expiry</span>
                        <span id="rev-passport-exp">—</span>
                    </div>
                    <div class="inv-review-row">
                        <span>Sponsor / Kafala</span>
                        <span id="rev-sponsor">—</span>
                    </div>
                </div>

                <div class="inv-nav">
                    <button type="button" class="btn btn-secondary inv-prev-btn" data-prev="3">
                        <i class="fa fa-arrow-left"></i> &nbsp;@lang('app.previous')
                    </button>
                    <button type="button" id="submit-signup" class="btn btn-primary">
                        @lang('app.signUp') &nbsp;<i class="fa fa-check"></i>
                    </button>
                </div>
            </div>

        </x-form>

        <div class="forgot_pswd">
            <div class="alert alert-success m-t-10 d-none" id="success-msg">"Your registration is complete. HR will review your details and activate your account shortly."</div>
        </div>

    @else
        <div class="alert alert-danger"></div>
    @endif

    <x-slot name="scripts">
        @if($isAllowedInCurrentPackage)
        <script>
        $(document).ready(function () {

            // ── STEP NAVIGATION ──────────────────────────────────
            var totalSteps = 4;

            function goToStep(step) {
                $('.inv-step').removeClass('active');
                $('#inv-step-' + step).addClass('active');

                for (var s = 1; s <= totalSteps; s++) {
                    var $s    = $('[data-step="' + s + '"]');
                    var $line = $('#line-' + s);
                    $s.removeClass('active done');
                    $line.removeClass('done');
                    if      (s < step)  { $s.addClass('done'); $line.addClass('done'); }
                    else if (s === step) { $s.addClass('active'); }
                }

                // Populate review on step 4
                if (step === 4) { populateReview(); }

                $('html, body').animate({ scrollTop: 0 }, 150);
            }

            // ── STEP VALIDATION ───────────────────────────────────
            function validateStep(step) {
                var ok = true;

                if (step === 1) {
                    var name = $.trim($('#user-name').val());
                    if (name === '') {
                        highlightError('#user-name', 'Full name is required.');
                        ok = false;
                    }
                }

                if (step === 2) {
                    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                    @if (!is_null($invite->email_restriction))
                        var emailUser = $.trim($('#email_address').val());
                        if (emailUser === '') {
                            highlightError('#email_address', 'Email is required.');
                            ok = false;
                        } else {
                            var fullEmail = emailUser + '@' + $('#email_domain').val();
                            $('#user-email').val(fullEmail);
                        }
                    @else
                        var email = $.trim($('#user-email').val());
                        if (email === '') {
                            highlightError('#user-email', 'Email is required.');
                            ok = false;
                        } else if (!emailReg.test(email)) {
                            highlightError('#user-email', 'Please enter a valid email address.');
                            ok = false;
                        }
                    @endif

                    if(ok == true){
                        var password = $('#password').val();
                        if (password.length < 6) {
                            highlightError('#password', 'Password must be at least 6 characters.');
                            ok = false;
                        }
                    }

                    @if ($globalSetting->sign_up_terms == 'yes')
                        if (!$('#read_agreement').is(':checked')) {
                            showAlert('Please accept the terms and conditions.');
                            ok = false;
                        }
                    @endif
                }

                if (step === 3) {
                    var iqamaNo = $.trim($('#iqama_no').val());
                    if (iqamaNo === '') {
                        highlightError('#iqama_no', 'Iqama No is required.');
                        ok = false;
                    }

                    if(ok == true){
                        // ADDED: Validation for Iqama Expiry Date
                        var iqamaExp = $.trim($('#iqama_expiry_date').val());
                        if (iqamaExp === '') {
                            highlightError('#iqama_expiry_date', 'Iqama Expiry Date is required.');
                            ok = false;
                        }
                    }

                    if(ok == true){
                        var iqamaProf = $.trim($('#iqama_profession').val());
                        if (iqamaProf === '') {
                            highlightError('#iqama_profession', 'Iqama Profession is required.');
                            ok = false;
                        }
                    }

                    if(ok == true){
                        var passport = $.trim($('#passport_no').val());
                        if (passport === '') {
                            highlightError('#passport_no', 'Passport No is required.');
                            ok = false;
                        }
                    }

                    if(ok == true){
                        // ADDED: Validation for Passport Expiry Date
                        var passportExp = $.trim($('#passport_expiry_date').val());
                        if (passportExp === '') {
                            highlightError('#passport_expiry_date', 'Passport Expiry Date is required.');
                            ok = false;
                        }
                    }

                    if(ok == true){
                        var sponsor = $.trim($('#sponsor_kafala').val());
                        if (sponsor === '') {
                            highlightError('#sponsor_kafala', 'Sponsor / Kafala is required.');
                            ok = false;
                        }
                    }
                }

                return ok;
            }

            function highlightError(selector, msg) {
                $(selector).css('border-color', '#dc3545');
                showAlert(msg);
                setTimeout(function () {
                    $(selector).css('border-color', '');
                }, 3000);
            }

            function showAlert(msg) {
                $('#alert').removeClass('d-none').html(msg);
                setTimeout(function () { $('#alert').addClass('d-none'); }, 4000);
            }

            // ── NEXT / PREV ───────────────────────────────────────
            $(document).on('click', '.inv-next-btn', function () {
                var next    = parseInt($(this).data('next'));
                var current = next - 1;
                if (!validateStep(current)) return;
                goToStep(next);
            });

            $(document).on('click', '.inv-prev-btn', function () {
                var prev = parseInt($(this).data('prev'));
                goToStep(prev);
            });

            // ── PHONE CODE AUTO-SELECT ────────────────────────────
            $('#country').change(function () {
                var phonecode = $(this).find(':selected').data('phonecode');
                $('#country_phonecode').val(phonecode);
            });

            // ── EMAIL DOMAIN CONCAT ───────────────────────────────
            $('#email_address').on('input change', function () {
                var fullEmail = $.trim($(this).val()) + '@' + $('#email_domain').val();
                $('#user-email').val(fullEmail);
            });

            // ── REVIEW POPULATE ───────────────────────────────────
            function populateReview() {
                $('#rev-name').text($('#user-name').val()          || '—');
                $('#rev-gender').text($('#gender').val()           || '—');
                $('#rev-dob').text($('#date_of_birth').val()       || '—');

                var phonecode = $('#country_phonecode').val();
                var mobile    = $('#mobile').val();
                $('#rev-mobile').text((phonecode && mobile) ? ('+' + phonecode + ' ' + mobile) : (mobile || '—'));

                @if (!is_null($invite->email_restriction))
                    $('#rev-email').text($('#user-email').val()    || '—');
                @else
                    $('#rev-email').text($('#user-email').val()    || '—');
                @endif

                $('#rev-iqama-no').text($('#iqama_no').val()       || '—');
                $('#rev-iqama-prof').text($('#iqama_profession').val() || '—');
                $('#rev-iqama-exp').text($('#iqama_expiry_date').val() || '—');
                $('#rev-passport-no').text($('#passport_no').val() || '—');
                $('#rev-passport-exp').text($('#passport_expiry_date').val() || '—');
                $('#rev-sponsor').text($('#sponsor_kafala').val()  || '—');
            }

            // ── SUBMIT ────────────────────────────────────────────
            $('#submit-signup').click(function () {
                var url = "{{ route('accept_invite') . '?invite=' . $invite->invitation_code }}";

                var formData = new FormData($('#acceptInviteForm')[0]);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        $('#submit-signup').prop('disabled', true).html(
                            '<i class="fa fa-spinner fa-spin"></i> Processing...'
                        );
                    },
                    success: function (response) {
                        if (response.status == 'fail' || response.status == 'error') {
                            $('#alert').removeClass('d-none').html(response.message);
                            $('#submit-signup').prop('disabled', false).html(
                                '@lang('app.signUp') <i class="fa fa-check"></i>'
                            );
                            return;
                        }
                        $('#success-msg').removeClass('d-none');
                        $('#acceptInviteForm').hide();
                        setTimeout(function () {
                            window.location.href = "{{ route('dashboard') }}";
                        }, 4000);
                    },
                    error: function (xhr) {
                        var msg = 'Something went wrong. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $('#alert').removeClass('d-none').html(msg);
                        $('#submit-signup').prop('disabled', false).html(
                            '@lang('app.signUp') <i class="fa fa-check"></i>'
                        );
                    }
                });
            });

        });
        </script>
        @endif

        @foreach ($frontWidgets as $item)
            @if(!is_null($item->footer_script))
                {!! $item->footer_script !!}
            @endif
        @endforeach
    </x-slot>
</x-auth>
