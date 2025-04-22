<?php

namespace App\View\Components\Navigation;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumb extends Component
{
    public $groups;
    public $breadcrumbs;

    /**
     * Create a new component instance.
     *
     * @param array $groups
     * @param array $breadcrumbs
     */
    public function __construct($groups = [], $breadcrumbs = [])
    {
        $this->groups = $groups;
        $this->breadcrumbs = $breadcrumbs;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.navigation.breadcrumb');
    }
}
