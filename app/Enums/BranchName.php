<?php

namespace App\Enums;

enum BranchName: string
{
    case JUBAIL = 'JUBAIL';
    case JEDDAH = 'JEDDAH';
    case RIYADH = 'RIYADH';
    case ALHASA = 'ALHASA';

    // This method is used to display the enum value in the user interface.
    public function label(): string
    {
        return match ($this) {
            self::JUBAIL => __('app.branch.jubail'),
            self::JEDDAH => __('app.branch.jeddah'),
            self::RIYADH => __('app.branch.riyadh'),
            self::ALHASA => __('app.branch.alhasa'),
            default => $this->value,
        };
    }

    // This method is return all the values as array.
    public static function toArray(): array
    {
        $driverTypes = [];

        foreach (DriverType::cases() as $status) {
            $driverTypes [] = $status->value;
        }

        return $driverTypes;
    }

}
