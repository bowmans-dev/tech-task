<?php

namespace App\View\Components\Navigation;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public $groups;
 
    public function __construct($groups = [])
    {
        $this->groups = $groups;
    }


    public function render(): View|Closure|string
    {
        return view('Components.Navigation.sidebar');
    }
}
