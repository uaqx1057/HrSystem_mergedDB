<?php

namespace App\Http\Controllers;

use App\DataTables\ReceiptVoucherDataTable;
use App\DataTables\VoucherDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\ReceiptVoucher\StoreRequest;
use App\Http\Requests\Admin\ReceiptVoucher\UpdateRequest;
use App\Models\{Business, Driver, DriverType, User, ReceiptVoucher};
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use App\Helper\Files;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class ReceiptVoucherController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.receipt_voucher';
        $this->middleware(function ($request, $next) {
            // abort_403(!in_array('receiptVoucher', $this->user->modules));
            return $next($request);
        });
    }

    public function getDriverType(Request $request)
    {
        return DriverType::find($request->id);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(VoucherDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_receipt_voucher');
        abort_403(!in_array($viewPermission, ['all']));

        return $dataTable->render('receipt-voucher.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $addPermission = user()->permission('add_receipt_voucher');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $this->pageTitle = __('app.addReceiptVoucher');
        $this->countries = countries();
        $this->view = 'receipt-voucher.ajax.create';
        $this->drivers = Driver::all();
        $lastVoucherId = ReceiptVoucher::orderBy('id', 'desc')->pluck('voucher_number')->first();
        $this->lastVoucherId = $lastVoucherId == 0 ? 1000 : $lastVoucherId + 1;
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('receipt-voucher.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $addPermission = user()->permission('add_receipt_voucher');
        abort_403(!in_array($addPermission, ['all', 'added']));
        DB::beginTransaction();
        try {

            $validated = $request->validated();
            $validated['voucher_date'] = Carbon::createFromFormat($this->company->date_format, $request->voucher_date)->format('Y-m-d');
            $validated['start_date'] = Carbon::createFromFormat($this->company->date_format, $request->start_date)->format('Y-m-d');
            $validated['end_date'] = Carbon::createFromFormat($this->company->date_format, $request->end_date)->format('Y-m-d');
            $validated['business_id'] = $request->business_id ? $request->business_id : 0;
            $validated['wallet_amount'] = $request->wallet_amount ? $request->wallet_amount : 0;
            $validated['other_business'] = $request->other_business ? $request->other_business : '';
            $validated['status'] = 'received';
            $validated['created_by'] = user()->id;

            ReceiptVoucher::create($validated);

            DB::commit();
        } catch (\Exception $e) {
            logger($e->getMessage());
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support ' . $e->getMessage());
        }

        if (request()->add_more == 'true') {
            $html = $this->create();
            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('receipt-voucher.index')]);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->viewPermission = user()->permission('view_receipt_voucher');
        // abort_403(!($this->viewPermission == 'all'));

        $this->receiptVoucher = ReceiptVoucher::with('driver', 'business', 'creator')->findOrFail($id);

        $this->receiptVoucherFirst = ReceiptVoucher::with('driver')->first();

        $this->bussiness = DB::table('business_driver')->where([
            'driver_id' => $this->receiptVoucher->driver_id,
            'business_id' => $this->receiptVoucher->business_id,
        ])->first();


        $tab = request('tab');

        $this->pageTitle = $this->receiptVoucher->driver->name;
        $this->view = 'receipt-voucher.ajax.show';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['views' => $this->view, 'status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->activeTab = $tab ?: 'profile';

        return view('receipt-voucher.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReceiptVoucher $receiptVoucher)
    {
        $this->editPermission = user()->permission('edit_receipt_voucher');
        // abort_403(!($this->editPermission == 'all'));

        $this->pageTitle = __('app.update');

        $this->receiptVoucher = $receiptVoucher;
        $this->drivers = Driver::all();
        $this->view = 'receipt-voucher.ajax.edit';

        if (request()->ajax()) {
            $html = view('receipt-voucher.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('receipt-voucher.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, ReceiptVoucher $receiptVoucher)
    {
        $this->editPermission = user()->permission('edit_receipt_voucher');
        // abort_403(!($this->editPermission == 'all'));
        $validated = $request->validated();
        $validated['voucher_date'] = Carbon::createFromFormat($this->company->date_format, $request->voucher_date)->format('Y-m-d');
        $validated['start_date'] = Carbon::createFromFormat($this->company->date_format, $request->start_date)->format('Y-m-d');
        $validated['end_date'] = Carbon::createFromFormat($this->company->date_format, $request->end_date)->format('Y-m-d');
        $validated['business_id'] = $request->business_id ? $request->business_id : 0;
        $validated['wallet_amount'] = $request->wallet_amount ? $request->wallet_amount : 0;
        $validated['other_business'] = $request->other_business ? $request->other_business : '';
        $validated['status'] = $request->status;
        $receiptVoucher->update($validated);

        return Reply::success(__('messages.updateSuccess'), ['redirectUrl' => route('receipt-voucher.index')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deletePermission = user()->permission('delete_receipt_voucher');
        abort_403(!($deletePermission == 'all'));

        // $this->receiptVoucher = ReceiptVoucher::findOrFail($id);

        // ReceiptVoucher::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function download($id)
    {
        try {
            $this->invoiceSetting = invoice_setting();
            $this->receipt_voucher = ReceiptVoucher::with('driver', 'business')->findOrFail($id);

            $this->font = match ($this->invoiceSetting->locale) {
                'ja' => 'ipag',
                'hi' => 'hindi',
                'th' => 'THSarabunNew',
                'vi' => 'BeVietnamPro',
                default => $this->invoiceSetting->is_chinese_lang ? 'SimHei' : 'Verdana',
            };



            if (!$this->receipt_voucher) {
                \Log::error('Receipt voucher not found for ID: ' . $id);
                abort(404, 'Receipt voucher not found');
            }

            App::setLocale($this->invoiceSetting->locale);
            Carbon::setLocale($this->invoiceSetting->locale);

            $pdf = app('dompdf.wrapper');
            $pdf->setOption('enable_php', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            $pdf->loadView('receipt-voucher.pdf.' . $this->invoiceSetting->template, $this->data);
            $filename = $this->receipt_voucher->voucher_number;


            // Generate PDF for download
            $pdfOption = [
                'pdf' => $pdf,
                'fileName' => $filename
            ];

            if (!$pdfOption || !isset($pdfOption['pdf']) || !isset($pdfOption['fileName'])) {
                \Log::error('Error generating PDF for Receipt Voucher ID: ' . $id);
                abort(500, 'Error generating PDF');
            }

            $pdf = $pdfOption['pdf'];
            $filename = $pdfOption['fileName'];

            return request()->view ? $pdf->stream($filename . '.pdf') : $pdf->download($filename . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Exception during download: ' . $e->getMessage());
            abort(500, 'Error occurred during download');
        }
    }

    public function domPdfObjectForDownload($id, $invoiceSetting)
    {
        $this->receipt_voucher = ReceiptVoucher::with('driver', 'business')->findOrFail($id);
        $this->bussiness = DB::table('business_driver')->where([
            'driver_id' => $this->receipt_voucher->driver_id,
            'business_id' => $this->receipt_voucher->business_id,
        ])->first();


        // App::setLocale($this->invoiceSetting->locale);
        // Carbon::setLocale($this->invoiceSetting->locale);

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('receipt-voucher.pdf.' . $this->invoiceSetting->template, $this->data);
        $filename = $this->receipt_voucher->voucher_number;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }
}
