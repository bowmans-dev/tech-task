<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectDropdown extends Component
{
    /**
     * Create a new component instance.
     */
    public $name;

    public $id;

    public $label;

    public $options;

    public $placeholder;

    public $selected;

    public $required;

    public function __construct($name, $id = null, $label = null, $options = [], $placeholder = null, $selected = null, $required = false)
    {
        $this->name = $name;
        $this->id = $id ?? $name;
        $this->label = $label;
        $this->options = $options;
        $this->placeholder = $placeholder;
        $this->selected = $selected;
        $this->required = $required;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.select-dropdown');
    }
}
