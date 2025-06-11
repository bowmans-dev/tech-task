<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEventTeamMember extends Model
{
    use HasFactory;

    protected $table = 'calendar_event_team_members';

    protected $fillable = ['calendar_event_id', 'user_id'];
}