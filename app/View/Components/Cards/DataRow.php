<?php

namespace App\View\Components\Cards;

use Illuminate\View\Component;

class DataRow extends Component
{

    public $label;
    public $value;
    public $html;
    public $background;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($label, $value, $html = false , $background = null)
    {
        $this->label = $label;
        $this->value = $value;
        $this->html = $html;
        $this->background = $background;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.cards.data-row');
    }

}
