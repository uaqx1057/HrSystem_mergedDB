{{-- Shared "Terminate employee" confirmation dialog. @include this INSIDE an
     existing <script> block on the employee list / pending-offboarding list.
     Requires SweetAlert2, jQuery, $.easyAjax and a showTable() in scope. --}}
    $('body').on('click', '.terminate-table-row', function () {
        var id = $(this).data('user-id');

        Swal.fire({
            title: 'Terminate employee?',
            width: 468,
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            reverseButtons: true,
            confirmButtonText: "@lang('messages.confirmTerminate')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                popup: 'term-swal',
                confirmButton: 'btn btn-primary px-4',
                cancelButton: 'btn btn-outline-secondary px-4 mr-2'
            },
            showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
            buttonsStyling: false,
            html:
                '<style>' +
                '.term-swal .swal2-html-container{margin:.4em 1.2em 0;overflow:visible}' +
                '.tm{text-align:left;font-size:13px;color:#374151}' +
                '.tm .tm-sub{color:#6b7280;font-size:12px;line-height:1.5;margin:-2px 0 14px}' +
                '.tm .tm-lbl{display:block;font-weight:600;color:#374151;margin:14px 0 6px;font-size:12px;text-transform:uppercase;letter-spacing:.03em}' +
                '.tm .tm-lbl .opt{font-weight:400;text-transform:none;letter-spacing:0;color:#9ca3af}' +
                '.tm textarea{width:100%;border:1px solid #d1d5db;border-radius:9px;padding:9px 11px;font-size:13px;resize:vertical;min-height:60px;background:#fff}' +
                '.tm textarea:focus{border-color:#1f7a4d;box-shadow:0 0 0 3px rgba(31,122,77,.12);outline:none}' +
                '.tm .tm-opts{display:flex;gap:8px}' +
                '.tm .tm-opt{flex:1;border:1px solid #d1d5db;border-radius:9px;padding:10px 12px;cursor:pointer;transition:border-color .15s,background .15s}' +
                '.tm .tm-opt:hover{border-color:#1f7a4d}' +
                '.tm .tm-opt input{display:none}' +
                '.tm .tm-opt .tm-t{font-weight:600;color:#374151;font-size:12.5px}' +
                '.tm .tm-opt .tm-d{color:#9ca3af;font-size:11px;margin-top:2px}' +
                '.tm .tm-opt.on{border-color:#1f7a4d;background:#ecfdf3}' +
                '.tm .tm-opt.on .tm-t{color:#1f7a4d}' +
                '.tm .tm-months{display:flex;gap:6px;margin-top:2px}' +
                '.tm .tm-months label{flex:1;text-align:center;border:1px solid #d1d5db;border-radius:9px;padding:8px 0;cursor:pointer;font-weight:600;color:#374151;font-size:12.5px;transition:.15s}' +
                '.tm .tm-months input{display:none}' +
                '.tm .tm-months label.on{border-color:#1f7a4d;background:#1f7a4d;color:#fff}' +
                '</style>' +
                '<div class="tm">' +
                '<div class="tm-sub">The request is sent to HR for approval. The last working day is calculated from the option chosen below.</div>' +
                '<label class="tm-lbl">Reason for termination <span class="opt">(optional)</span></label>' +
                '<textarea id="terminate_reason" placeholder="e.g. Role redundant after restructuring"></textarea>' +
                '<label class="tm-lbl">Effect</label>' +
                '<div class="tm-opts">' +
                '<label class="tm-opt" data-v="notice"><input type="radio" name="tm_effect" value="notice" checked>' +
                '<div class="tm-t">With notice period</div><div class="tm-d">Serve 1&ndash;3 months</div></label>' +
                '<label class="tm-opt" data-v="immediate"><input type="radio" name="tm_effect" value="immediate">' +
                '<div class="tm-t">Immediate effect</div><div class="tm-d">Last day is today</div></label>' +
                '</div>' +
                '<div id="tm_months_wrap"><label class="tm-lbl">Notice period</label>' +
                '<div class="tm-months">' +
                '<label data-m="1"><input type="radio" name="tm_months" value="1" checked><span>1 month</span></label>' +
                '<label data-m="2"><input type="radio" name="tm_months" value="2"><span>2 months</span></label>' +
                '<label data-m="3"><input type="radio" name="tm_months" value="3"><span>3 months</span></label>' +
                '</div></div>' +
                '</div>',
            didOpen: function (popup) {
                var $p = $(popup);
                function syncEffect() {
                    var v = $p.find('input[name=tm_effect]:checked').val();
                    $p.find('.tm-opt').each(function () { $(this).toggleClass('on', $(this).data('v') === v); });
                    $p.find('#tm_months_wrap').toggle(v === 'notice');
                }
                function syncMonths() {
                    var m = $p.find('input[name=tm_months]:checked').val();
                    $p.find('.tm-months label').each(function () { $(this).toggleClass('on', String($(this).data('m')) === m); });
                }
                $p.on('change', 'input[name=tm_effect]', syncEffect);
                $p.on('change', 'input[name=tm_months]', syncMonths);
                syncEffect();
                syncMonths();
            },
            preConfirm: function () {
                var nt = document.querySelector('input[name=tm_effect]:checked').value;
                return {
                    reason: document.getElementById('terminate_reason').value,
                    noticeType: nt,
                    noticeMonths: nt === 'notice' ? document.querySelector('input[name=tm_months]:checked').value : ''
                };
            }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            var url = "{{ route('employees.terminate-pending', ':id') }}".replace(':id', id);
            $.easyAjax({
                type: 'POST',
                url: url,
                blockUI: true,
                data: {
                    '_token': "{{ csrf_token() }}",
                    '_method': 'POST',
                    'terminate_reason': result.value.reason,
                    'notice_type': result.value.noticeType,
                    'notice_months': result.value.noticeMonths
                },
                success: function (response) {
                    if (response.status === 'success') {
                        showTable();
                    }
                }
            });
        });
    });
