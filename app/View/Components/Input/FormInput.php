<?php

namespace App\View\Components\Input;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormInput extends Component
{
    public $id;

    public $name;

    public $label;

    public $type;

    public $pattern;

    public function __construct($id, $name, $label, $type = 'text', $pattern = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->label = $label;
        $this->type = $type;
        $this->pattern = $pattern;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.input.form-input');
    }
}
