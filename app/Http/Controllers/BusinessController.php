<?php

namespace App\Http\Controllers;

use App\DataTables\BusinessesDataTable;
use App\Enums\Salutation;
use App\Helper\Reply;
use App\Http\Requests\Admin\Business\{StoreRequest, UpdateRequest};
use App\Models\{LanguageSetting, DriverCalculation, Role, Business, BusinessField};
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.businesses';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('businesses', $this->user->modules));

            return $next($request);
        });
    }

    public function ajaxLoadBusiness(Request $request)
    {
        $search = $request->search;

        $businesses = Business::orderby('name')
            ->select('id', 'name')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->take(20)
            ->get();

        $response = array();

        foreach ($businesses as $business) {

            $response[] = array(
                'id' => $business->id,
                'text' => $business->name
            );

        }

        return response()->json($response);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(BusinessesDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_businesses');
        abort_403(!in_array($viewPermission, ['all']));

        return $dataTable->render('businesses.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $addPermission = user()->permission('add_businesses');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $this->pageTitle = __('app.addProject');
        $this->view = 'businesses.ajax.create';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }
        return view('businesses.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        // return $request->all();
        $addPermission = user()->permission('add_businesses');
        abort_403(!in_array($addPermission, ['all', 'added']));

        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $fields = isset($validated['fields']) ? $validated['fields'] : [];

            unset($validated['fields']);

            $fields[] = [
                'admin_only' => '0',
                'name' => 'Total Orders',
                'required' => '1',
                'type' => 'INTEGER'
            ];
            $fields[] = [
                'admin_only' => '0',
                'name' => 'Bonus',
                'required' => '0',
                'type' => 'INTEGER'
            ];
            $fields[] = [
                'admin_only' => '0',
                'name' => 'Tip',
                'required' => '0',
                'type' => 'INTEGER'
            ];
            $fields[] = [
                'admin_only' => '0',
                'name' => 'Other Tip',
                'required' => '0',
                'type' => 'INTEGER'
            ];
            $business = Business::create($validated);
            $business->fields()->createMany($fields);

            // Storing Driver Calculations
            if($request->has('calculation_type')){
                foreach($request->calculation_type as $key => $type){
                    if($type == 'RANGE' && $request->amount_field[$key] != null && $request->range_from[$key] != null && $request->range_to[$key] != null){
                        $business->driver_calculations()->create([
                            'type' => $type,
                            'amount' => $request->amount_field[$key],
                            'from_value' => $request->range_from[$key],
                            'to_value' => $request->range_to[$key],
                        ]);
                    }elseif($type == 'EQUAL & ABOVE' && $request->amount_field[$key] != null && $request->equal_and_above[$key] != null){
                        $business->driver_calculations()->create([
                            'type' => $type,
                            'amount' => $request->amount_field[$key],
                            'value' => $request->equal_and_above[$key],
                        ]);
                    }elseif($type == 'FIXED' && $request->amount_field[$key] != null){
                        $business->driver_calculations()->create([
                            'type' => $type,
                            'amount' => $request->amount_field[$key],
                            'value' => 1,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            logger($e->getMessage());
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support '. $e->getMessage());
        }


        if (request()->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('businesses.index')]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_businesses');
        abort_403(!($this->editPermission == 'all'));

        $this->pageTitle = _('app.update');
        $this->business = Business::with('driver_calculations')->find($id);
        $this->view = 'businesses.ajax.edit';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('businesses.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Business $business)
    {
        $this->editPermission = user()->permission('edit_businesses');
        abort_403(!($this->editPermission == 'all'));

        $validated = $request->validated();
        $fields = $validated['fields'];
        unset($validated['fields']);

        $business->update($validated);

        if ($request->has('fields')) {
            foreach ($request->fields as $id => $field) {
                BusinessField::where('id', $id)->update([
                    'name' => $field['name'],
                    'required' => $field['required'],
                    'admin_only' => $field['admin_only']
                ]);
            }
        }

        $business->driver_calculations()->delete();
         // Storing Driver Calculations
            if($request->has('calculation_type')){
                foreach($request->calculation_type as $key => $type){
                    if($type == 'RANGE' && $request->amount_field[$key] != null && $request->range_from[$key] != null && $request->range_to[$key] != null){
                        $business->driver_calculations()->create([
                            'type' => $type,
                            'amount' => $request->amount_field[$key],
                            'from_value' => $request->range_from[$key],
                            'to_value' => $request->range_to[$key],
                        ]);
                    }elseif($type == 'EQUAL & ABOVE' && $request->amount_field[$key] != null && $request->equal_and_above[$key] != null){
                        $business->driver_calculations()->create([
                            'type' => $type,
                            'amount' => $request->amount_field[$key],
                            'value' => $request->equal_and_above[$key],
                        ]);
                    }elseif($type == 'FIXED' && $request->amount_field[$key] != null){
                        $business->driver_calculations()->create([
                            'type' => $type,
                            'amount' => $request->amount_field[$key],
                            'value' => 1,
                        ]);
                    }
                }
            }

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deletePermission = user()->permission('delete_businesses');
        abort_403(!($deletePermission == 'all'));

        $this->businesses = Business::findOrFail($id);

        Business::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }
}
