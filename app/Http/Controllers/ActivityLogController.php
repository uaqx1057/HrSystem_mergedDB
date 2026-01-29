<?php

namespace App\Http\Controllers;

use App\DataTables\ActivityLogDataTable;
use App\Traits\ImportExcel;

class ActivityLogController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.activity';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ActivityLogDataTable $dataTable)
    {
        // $viewPermission = user()->permission('view_businesses');
        abort_403(!in_array('admin', user_roles()));

        return $dataTable->render('activity-log.index', $this->data);
    }
}
