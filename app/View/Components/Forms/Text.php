<?php

namespace App\View\Components\Forms;

use Illuminate\View\Component;

class Text extends Component
{

    public $fieldLabel;
    public $fieldRequired;
    public $fieldPlaceholder;
    public $fieldValue;
    public $fieldName;
    public $fieldId;
    public $fieldHelp;
    public $fieldReadOnly;
    public $popover;

    public $textColor;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($fieldLabel, $fieldName, $fieldId, $fieldRequired = false, $fieldPlaceholder = null, $fieldValue = null, $fieldHelp = null, $fieldReadOnly = false, $popover = null, $textColor = null)
    {
        $this->fieldLabel = $fieldLabel;
        $this->fieldRequired = $fieldRequired;
        $this->fieldPlaceholder = $fieldPlaceholder;
        $this->fieldValue = $fieldValue;
        $this->fieldName = $fieldName;
        $this->fieldId = $fieldId;
        $this->fieldHelp = $fieldHelp;
        $this->fieldReadOnly = $fieldReadOnly;
        $this->popover = $popover;
        $this->textColor = $textColor;

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.forms.text');
    }

}
