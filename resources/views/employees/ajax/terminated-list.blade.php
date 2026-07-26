<!-- ROW START -->
<div class="row ">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">

        <form action="" id="filter-form">
            <div class="row pb-3">
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

                <!-- RESET START -->
                <div class="col-md-3 col-sm-6 d-flex px-lg-2 px-md-2 px-0 mt-0 mt-lg-4 mt-md-4">
                    <x-forms.button-secondary class="btn-xs d-none height-35" id="reset-filters" icon="times-circle">
                        @lang('app.clearFilters')
                    </x-forms.button-secondary>
                </div>
                <!-- RESET END -->
            </div>
        </form>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
</div>

@include('sections.datatable_js')

<script>
    $('#employees-table').on('preXhr.dt', function(e, settings, data) {
        const searchText = $('#search-text-field').val();
        data['searchText'] = searchText;
    });

    const showTable = () => {
        window.LaravelDataTables["employees-table"].draw(false);
    }

    $('#search-text-field').on('keyup', function() {
        if ($('#search-text-field').val() != "") {
            $('#reset-filters').removeClass('d-none');
        } else {
            $('#reset-filters').addClass('d-none');
        }
        showTable();
    });

    $('#reset-filters, #reset-filters-2').click(function() {
        $('#filter-form')[0].reset();
        $('#reset-filters').addClass('d-none');
        showTable();
    });
</script>
