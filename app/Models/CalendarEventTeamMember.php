<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEventTeamMember extends Model
{
    use HasFactory;

    // Specify the table if it doesn't follow Laravel's naming convention
    protected $table = 'calendar_event_team_members';

    // Allow mass assignment for these fields
    protected $fillable = ['calendar_event_id', 'user_id'];
}