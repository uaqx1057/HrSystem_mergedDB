<script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>

<script type="text/javascript">
    $(function() {

        var start = moment().subtract(89, 'days');
        var end = moment();

        $('#datatableRange').daterangepicker({
            autoUpdateInput: false,
            locale: daterangeLocale,
            linkedCalendars: false,
            startDate: start,
            endDate: end,
            showDropdowns: true,
            ranges: daterangeConfig
        }, cb);

        $('#branch_id').change(function(){
            const branch_id = $(this).val();
            const business_id = $('#business_id').val();
            const driver_type_id = $('#driver_type_id').val();
            const date = $('#datatableRange').val().split(' ')
            const startDate = date[0];
            const endDate = date[2];
            showTable();
            fetchStats({startDate, endDate, business_id, driver_type_id, branch_id});
        });

        $('#driver_type_id').change(function(){
            const driver_type_id = $(this).val();
            const business_id = $('#business_id').val();
            const branch_id = $('#branch_id').val();
            const date = $('#datatableRange').val().split(' ')
            const startDate = date[0];
            const endDate = date[2];
            showTable();
            fetchStats({startDate, endDate, business_id, driver_type_id, branch_id});
        });

        $('#business_id').change(function(){
            const business_id = $(this).val();
            const driver_type_id = $('#driver_type_id').val();
            const branch_id = $('#branch_id').val();
            const date = $('#datatableRange').val().split(' ')
            const startDate = date[0];
            const endDate = date[2];
            showTable();
            fetchStats({startDate, endDate, business_id, driver_type_id, branch_id});
        });

        $('#datatableRange').on('apply.daterangepicker', function(ev, picker) {
            const date = $('#datatableRange').val().split(' ')
            const startDate = date[0];
            const endDate = date[2];
            const business_id = $('#business_id').val();
            const driver_type_id = $('#driver_type_id').val();
            const branch_id = $('#branch_id').val();
            showTable();
            fetchStats({startDate, endDate, business_id, driver_type_id, branch_id});
        });

        function fetchStats(data){
            $.ajax({
                url:"{{ route('dms.revenue-reporting.get-content') }}",
                method:"GET",
                data,
                success:function(res){
                    console.log(res)
                    $('#totalRevenue').html(res.total_revenue);
                    $('#totalCost').html(res.total_cost);
                    $('#grossProfit').html(res.gross_profit);
                    $('#totalOrders').html(res.total_orders);

                    let html = ``;

                    res.businesses.map(business => {
                        html += `
                        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                            <a href="javascript:;">
                                <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                                    <div class="d-block text-capitalize">
                                        <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">${business.name}

                                        </h5>
                                        <div class="d-flex">
                                            <p class="mb-0 f-15 font-weight-bold text-blue text-primary d-grid"><span
                                                    >${business.total_orders}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="d-block">
                                        <i class="fa fa-clock text-lightest f-18"></i>
                                    </div>
                                </div>

                            </a>
                        </div>
                        `;
                    })

                    $('#business_with_orders').html(html);

                },error:function(xhr){
                    console.log(xhr.responseText);
                }
            });
        }

    });

</script>
