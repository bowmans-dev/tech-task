<?php

namespace App\View\Components\Navigation;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GroupAccordion extends Component
{
    public $groups;

    public function __construct($groups = [])
    {
        $this->groups = $groups;
    }

    
    public function render()
    {
        return view('Components.Navigation.group-accordion');
    }
}