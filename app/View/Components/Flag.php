<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Flag extends Component
{

    public $country;
    public $textColor;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($country, $textColor = null)
    {
        $this->country = $country;
        $this->textColor = $textColor;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.flag');
    }

}
