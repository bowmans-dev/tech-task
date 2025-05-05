<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarEventTeamMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_event_team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_event_id'); // Foreign key for the event
            $table->unsignedBigInteger('user_id'); // Foreign key for the user
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('calendar_event_id')->references('id')->on('calendar')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Composite unique index (optional if you want unique relationships)
            $table->unique(['calendar_event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_event_team_members');
    }
}