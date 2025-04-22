<?php

namespace App\View\Components\Users;

use Illuminate\View\Component;

class UserList extends Component
{
    public $users; // Declare the property

    /**
     * Create a new component instance.
     *
     * @param  mixed  $users
     */
    public function __construct($users = [])
    {
        $this->users = $users;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.users.user-list');
    }
}
