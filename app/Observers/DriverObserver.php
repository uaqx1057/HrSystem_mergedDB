<?php

namespace App\Observers;

use App\Models\Driver;

class DriverObserver
{

    public function saving(Driver $driver)
    {
    }

    public function creating(Driver $driver)
    {
        if (company()) {
            $driver->company_id = company()->id;
        }
    }

    public function updated(Driver $driver)
    {
    }

    public function created(Driver $driver)
    {

    }

    public function deleting(Driver $driver)
    {
     
    }

    public function deleted(Driver $driver)
    {
    }

}
