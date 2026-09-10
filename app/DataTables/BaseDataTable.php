<?php

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\File;

class BaseDataTable extends DataTable
{

    protected $company;
    public $user;
    public $domHtml;

    public function __construct()
    {
        $this->company = company();
        $this->user = user();
        $this->domHtml = "<'row'<'col-sm-12'tr>><'d-flex'<'flex-grow-1'l><i><p>>";
    }

    public function setBuilder($table, $orderBy = 1)
    {
        $intl = File::exists(public_path('i18n/' . user()->locale . '.json')) ? asset('i18n/' . user()->locale . '.json') : __('app.datatable');

        return parent::builder()
            ->setTableId($table)
            ->columns($this->getColumns()) /** @phpstan-ignore-line */
            ->minifiedAjax()
            ->orderBy($orderBy)
            ->destroy(true)
            ->responsive()
            ->serverSide()
            ->stateSave(false)
            // global_setting() is cache-backed (not session-cached like company()),
            // so an admin changing the row limit takes effect without a re-login.
            ->pageLength((int) (global_setting()->datatable_row_limit ?: 25))
            ->processing()
            ->dom($this->domHtml)
            ->language($intl);
    }

    protected function filename(): string
    {
        // Remove DataTable from name
        $filename = str()->snake(class_basename($this), '-');

        return str_replace('data-table', '', $filename)  . now()->format('Y-m-d-H-i-s');
    }

}
