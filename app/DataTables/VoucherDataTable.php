<?php

namespace App\DataTables;

use App\Models\ReceiptVoucher;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class VoucherDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('received_from_driver', function ($row) {
                return $row->driver->name ?? '';
            })
            ->addColumn('iqaama_number', function ($row) {
                return $row->driver->iqaama_number ?? '';
            })
            ->addColumn('voucher_date', function ($row) {
                return $row->voucher_date->format('Y-m-d');
            })
            ->addColumn('action', 'receipt-voucher.datatable.action')
            ->setRowId('id')
            ->rawColumns(['received_from_driver', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ReceiptVoucher $model): QueryBuilder
    {
        return $model->with('driver')->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('receipt-voucher-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('create'),
                        Button::make('export'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('voucher_number'),
            Column::make('voucher_date'),
            Column::make('received_from_driver'),
            Column::make('iqaama_number'),
            Column::make('total_amount'),
            Column::make('status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ReceiptVoucher_' . date('YmdHis');
    }
}
