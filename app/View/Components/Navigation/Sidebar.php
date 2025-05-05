<?php

namespace App\View\Components\Navigation;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public $groups;

    /**
     * Create a new component instance.
     *
     * @param array $groups
     */
    public function __construct($groups = [])
    {
        $this->groups = $groups;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('Components.Navigation.sidebar');
    }
}
