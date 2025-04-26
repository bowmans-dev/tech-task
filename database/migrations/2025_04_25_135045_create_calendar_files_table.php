<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarFilesTable extends Migration
{
    public function up()
    {
        Schema::create('calendar_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained('calendars')->onDelete('cascade');  // Foreign key to the calendar table
            $table->string('file_path');  // File path
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('calendar_files');
    }
}
