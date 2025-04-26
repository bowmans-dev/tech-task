<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $table = 'calendar';

    protected $fillable = [
        'id', 'event_name', 'user_id', 'event_date', 'event_time', 'all_day',
    ];

    public function files()
    {
        return $this->hasMany(CalendarFile::class, 'calendar_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}