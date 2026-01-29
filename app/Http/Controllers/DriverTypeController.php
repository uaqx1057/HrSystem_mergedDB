<?php

namespace App\Http\Controllers;

use App\DataTables\BusinessesDriverTypesDataTable;
use App\DataTables\DriverTypesDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\DriverType\StoreRequest;
use App\Models\DriverType;
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use App\Helper\Files;
use App\Http\Requests\Admin\Driver\UpdateRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DriverTypeController extends AccountBaseController
{
    use ImportExcel;

    public function __construct(private BusinessesDriverTypesDataTable $businessDataTable)
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.driver_types';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('drivers', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(DriverTypesDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_driver_types');
        abort_403(!in_array($viewPermission, ['all']));
        return $dataTable->render('driver-types.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $addPermission = user()->permission('add_driver_types');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $this->pageTitle = __('app.addDriverType');
        $this->countries = countries();
        $this->view = 'driver-types.ajax.create';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('driver-types.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $addPermission = user()->permission('add_driver_types');
        abort_403(!in_array($addPermission, ['all', 'added']));

        DB::beginTransaction();
        try {
            $request['fields'] = implode(',', $request->fields);
            $request['is_freelancer'] = $request->is_freelancer == 'on' ? 1 : 0;
            DriverType::create($request->all());
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

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('driver-types.index')]);
    }

    public function ajaxLoadDriver(Request $request)
    {
        $search = $request->search;

        $drivers = DriverType::orderby('name')
            ->select('id', 'name')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->take(20)
            ->get();

        $response = array();

        foreach ($drivers as $driver) {

            $response[] = array(
                'id' => $driver->id,
                'text' => $driver->name
            );

        }

        return response()->json($response);
    }

    public function ajaxLoadLinkedDriver(Request $request)
    {
        $this->linkDriverPermission = user()->permission('add_link_driver') == 'all' || user()->is_superadmin;
        abort_403(!($this->linkDriverPermission));

        $search = $request->search;

        $drivers = (user()->is_superadmin ? DriverType::all() : user()->drivers())
            ->orderby('name')
            ->select('drivers.id', 'drivers.name')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->take(20)
            ->get();

        $response = array();

        foreach ($drivers as $driver) {

            $response[] = array(
                'id' => $driver->id,
                'text' => $driver->name
            );

        }

        return response()->json($response);
    }


    public function businesses()
    {
        $tab = request('tab');
        $this->activeTab = $tab ?: 'businesses';
        $this->view = 'drivers.ajax.businesses';

        return $this->businessDataTable->with('driver_id', $this->driver->id)->render('drivers.show', $this->data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DriverType $driver_type)
    {
        $this->editPermission = user()->permission('edit_driver_types');
        abort_403(!($this->editPermission == 'all'));

        $this->pageTitle = __('app.update');

        $this->driver_type = $driver_type;
        $this->fields = explode(',', $driver_type->fields);
        $this->view = 'driver-types.ajax.edit';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }
        return view('driver-types.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, DriverType $driver_type)
    {
        $this->editPermission = user()->permission('edit_driver_types');
        abort_403(!($this->editPermission == 'all'));

        $request['fields'] = implode(',', $request->fields);
        $request['is_freelancer'] = $request->is_freelancer == 'on' ? 1 : 0;
        $driver_type->update($request->all());
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deletePermission = user()->permission('delete_driver_types');
        abort_403(!($deletePermission == 'all'));

        $this->driver = DriverType::findOrFail($id);

        DriverType::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }
}
