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
use App\Mail\AirTicketRequest;
use App\Mail\AirTicketStatusUpdate;
use Illuminate\Support\Facades\Mail;

class AirTicketController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.airTickets');
    }
    /**
     * @param AirTicketDataTable $dataTable
     * @return mixed|void
     */


    public function index(AirTicketDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

        $this->assignRole = user()->roles->pluck('name')->toArray();

        $this->employees = User::allEmployees();
        return $dataTable->render('air-tickets.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $viewPermission = user()->permission('add_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

        $this->assignRole = user()->roles->pluck('name')->toArray();

        $today = now();

        $eligibleEmployees = User::with(['employeeDetails', 'airTicket'])
            ->whereHas('employeeDetails', function ($query) use ($today) {
                // Only employees who have completed at least 1 year
                $query->where('joining_date', '<=', $today->copy()->subYear());
            });

        if(count($this->assignRole) < 2){
            $eligibleEmployees = $eligibleEmployees->where('id', user()->id);
        }
        $this->employees = $eligibleEmployees->get()
            ->filter(function ($employee) use ($today) {
                $joiningDate = $employee->employeeDetails->joining_date;

                if (!$joiningDate)
                    return false;

                // How many full years the employee has completed
                $yearsCompleted = (int) \Carbon\Carbon::parse($joiningDate)->diffInYears($today);

                // How many air tickets they have received
                $ticketsReceived = $employee->airTicket->where('status','!==', 'rejected')->count();

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
        $viewPermission = user()->permission('add_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

        $today = now();

        $eligibleEmployees = User::with(['employeeDetails', 'airTicket'])
            ->whereHas('employeeDetails', function ($query) use ($today) {
                // Only employees who have completed at least 1 year
                $query->where('joining_date', '<=', $today->copy()->subYear());
            })->where('id', $request->employee)->get()
            ->filter(function ($employee) use ($today) {
                $joiningDate = $employee->employeeDetails->joining_date;

                if (!$joiningDate)
                    return false;

                $yearsCompleted = (int) \Carbon\Carbon::parse($joiningDate)->diffInYears($today);

                $ticketsReceived = $employee->airTicket->where('status','!==', 'rejected')->count();

                return $ticketsReceived < $yearsCompleted;
            });


        if(count($eligibleEmployees) <= 0){
            return Reply::error('messages.invalidAirticket');
        }

        $ticket = new AirTicket();
        $ticket->employee_id = $request->employee;
        $ticket->date = $request->date;
        $ticket->status = $request->status ?? 'pending';
        $ticket->save();

        // Logic for sending email (Exactly like Advance Salary)
        $this->assignRole = user()->roles->pluck('name')->toArray();

        if (!in_array('admin', $this->assignRole)) {
            // Notify Admins
            $adminUsers = User::allAdmins(user()->company->id);
            foreach ($adminUsers as $admin) {
                Mail::to($admin->email)->send(new AirTicketRequest($ticket));
            }
        } else {
            // If Admin created it and it's not pending, notify employee
            if($ticket->status !== 'pending' && $ticket->employee->email){
                Mail::to($ticket->employee->email)->send(new AirTicketStatusUpdate($ticket));
            }
        }

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
        $viewPermission = user()->permission('view_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

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
        $viewPermission = user()->permission('edit_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

        $this->assignRole = user()->roles->pluck('name')->toArray();

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
                $ticketsReceived = $employee->airTicket->where('status','!==', 'rejected')->count();

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
        $viewPermission = user()->permission('edit_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

        $ticket = AirTicket::findOrFail($id);

        $ticket->employee_id = $request->employee;
        $ticket->date = $request->date;
        $ticket->status = $request->status ?? $ticket->status;

        $ticket->save();

        $redirectUrl = route('air-tickets.index');

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $viewPermission = user()->permission('delete_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

        AirTicket::findOrFail($id)->delete();

        $redirectUrl = route('air-tickets.index');

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        $viewPermission = user()->permission('delete_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

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

    public function approveTicket(Request $request)
    {
        $viewPermission = user()->permission('approve_or_reject_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

        $this->ticketID = $request->ticket_id;
        $this->ticketAction = $request->ticket_action; // This is 'approved'
        return view('air-tickets.approve.index', $this->data);
    }

    public function rejectTicket(Request $request)
    {
        $viewPermission = user()->permission('approve_or_reject_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

        $this->ticketID = $request->ticket_id;
        $this->ticketAction = $request->ticket_action; // This is 'rejected'
        return view('air-tickets.reject.index', $this->data);
    }

    public function ticketAction(Request $request)
    {
        $viewPermission = user()->permission('approve_or_reject_air_tickets');

        abort_403(!in_array($viewPermission, ['all']));

        $ticket = AirTicket::with('employee')->findOrFail($request->ticketId);
        $ticket->status = $request->action;

        if ($request->action == 'approved') {
            $ticket->approve_reason = $request->approveReason;
        } else {
            $ticket->reject_reason = $request->reason;
        }

        $ticket->approved_by = user()->id;
        $ticket->save();

        // Send email to the employee (Exactly like Advance Salary)
        if ($ticket->employee && $ticket->employee->email) {
            Mail::to($ticket->employee->email)->send(new AirTicketStatusUpdate($ticket));
        }

        return Reply::success(__('messages.updateSuccess'));
    }
}
