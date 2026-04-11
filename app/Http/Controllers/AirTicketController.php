<?php

namespace App\Http\Controllers;

use App\DataTables\AirTicketDataTable;
use App\Helper\Reply;
use App\Http\Requests\AirTicket\StoreAirTicket;
use App\Http\Requests\AirTicket\UpdateAirTicket;
use App\Models\AirTicket;
use App\Models\Insurance;
use App\Models\User;
use Illuminate\Http\Request;

class AirTicketController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.airTickets');

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));

            return $next($request);
        });
    }
    /**
     * @param AirTicketDataTable $dataTable
     * @return mixed|void
     */


    public function index(AirTicketDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_employees');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->employees = User::allEmployees();
        return $dataTable->render('air-tickets.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $today = now();

        $this->employees = User::with(['employeeDetails', 'airTicket'])
            ->whereHas('employeeDetails', function ($query) use ($today) {
                // Only employees who have completed at least 1 year
                $query->where('joining_date', '<=', $today->copy()->subYear());
            })
            ->get()
            ->filter(function ($employee) use ($today) {
                $joiningDate = $employee->employeeDetails->joining_date;

                if (!$joiningDate)
                    return false;

                // How many full years the employee has completed
                $yearsCompleted = (int) \Carbon\Carbon::parse($joiningDate)->diffInYears($today);

                // How many air tickets they have received
                $ticketsReceived = $employee->airTicket->count();

                // Fetch only if they are owed more tickets than they have
                return $ticketsReceived < $yearsCompleted;
            });

        if (request()->ajax()) {
            $html = view('air-tickets.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'air-tickets.ajax.create';

        return view('air-tickets.create', $this->data);
    }

    /**
     * @param StoreAirTicket $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreAirTicket $request)
    {
        // dd($request);
        $ticket = new AirTicket();
        $ticket->employee_id = $request->employee;
        $ticket->date = $request->date;
        $ticket->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('air-tickets.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->airTicket = AirTicket::with(['employee'])->findOrFail($id);
        if (request()->ajax()) {
            $html = view('air-tickets.ajax.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'air-tickets.ajax.show';

        return view('air-tickets.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->airTicket = AirTicket::findOrFail($id);
        $today = now();
        $currentEmployeeId = $this->airTicket->employee_id; // ✅ Store for use in filter

        $this->employees = User::with(['employeeDetails', 'airTicket'])
            // ✅ Removed where('id', '!=', ...) so current employee is included
            ->whereHas('employeeDetails', function ($query) use ($today) {
                $query->where('joining_date', '<=', $today->copy()->subYear());
            })
            ->get()
            ->filter(function ($employee) use ($today, $currentEmployeeId) {
                $joiningDate = $employee->employeeDetails->joining_date;

                if (!$joiningDate)
                    return false;

                $yearsCompleted = (int) \Carbon\Carbon::parse($joiningDate)->diffInYears($today);
                $ticketsReceived = $employee->airTicket->count();

                // ✅ Subtract 1 for current employee so they pass the eligibility check
                if ($employee->id == $currentEmployeeId) {
                    $ticketsReceived -= 1;
                }

                return $ticketsReceived < $yearsCompleted;
            });

        if (request()->ajax()) {
            $html = view('air-tickets.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'air-tickets.ajax.edit';

        return view('air-tickets.create', $this->data);
    }

    /**
     * @param UpdateAirTicket $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateAirTicket $request, $id)
    {
        $editDepartment = user()->permission('edit_employees');
        abort_403($editDepartment != 'all');

        $ticket = AirTicket::findOrFail($id);

        $ticket->employee_id = $request->employee;
        $ticket->date = $request->date;

        $ticket->save();

        $redirectUrl = route('air-tickets.index');

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_employees');
        abort_403($deletePermission != 'all');

        AirTicket::findOrFail($id)->delete();

        $redirectUrl = route('air-tickets.index');

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        $ids = explode(',', $request->row_ids);

        if ($request->action_type === 'delete') {
            AirTicket::whereIn('id', $ids)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Records deleted successfully.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid action.']);
    }
}
