<?php

namespace App\Enums;

enum Salutation: string
{
    // phpcs:disable
    case Mr = 'Mr';
    case Mrs = 'Mrs';
    case Miss = 'Miss';
    case Dr = 'Dr';
    case Sir = 'Sir';
    case Madam = 'Madam';
    case mr = 'mr';
    case mrs = 'mrs';
    case miss = 'miss';
    case dr = 'dr';
    case sir = 'sir';
    case madam = 'madam';
    // phpcs:enable

    // This method is used to display the enum value in the user interface.
    public function label(): string
    {
        return match ($this) {
            self::Mr => __('app.' . $this->value),
            self::Mrs => __('app.' . $this->value),
            self::Miss => __('app.' . $this->value),
            self::Dr => __('app.' . $this->value),
            self::Sir => __('app.' . $this->value),
            self::Madam => __('app.' . $this->value),
            default => $this->value,
        };
    }

}

