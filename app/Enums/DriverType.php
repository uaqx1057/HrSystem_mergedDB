<?php

namespace App\Enums;

enum DriverType: string
{
    case FREELANCER = 'FREELANCER';
    case IQAAMA = 'IQAAMA';

    // This method is used to display the enum value in the user interface.
    public function label(): string
    {
        return match ($this) {
            self::FREELANCER => __('app.driverType.freelancer'),
            self::IQAAMA => __('app.driverType.iqaama'),
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
