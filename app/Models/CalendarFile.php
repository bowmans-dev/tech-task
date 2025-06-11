<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarFile extends Model
{
    protected $fillable = ['calendar_id','file_name','file_path','uploaded_at'];

    public $timestamps = false;


    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }
}
