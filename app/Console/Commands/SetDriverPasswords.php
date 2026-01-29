<?php

namespace App\Console\Commands;

use App\Models\Driver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetDriverPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:set-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set passwords for drivers using their iqaama_number';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $drivers = Driver::all();

        foreach ($drivers as $driver) {
            $driver->password = Hash::make($driver->iqaama_number);
            $driver->save();
        }

        $this->info('Driver passwords have been set successfully.');
    }
}
