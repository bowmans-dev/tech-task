<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePollVotesTable extends Migration
{
    public function up()
    {
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_option_id')->constrained('poll_options')->onDelete('cascade');
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
            $table->morphs('voter'); // voter_id and voter_type
            $table->timestamps();

            $table->unique(['message_id', 'voter_id', 'voter_type']); // one vote per poll per user/admin
        });
    }

    public function down()
    {
        Schema::dropIfExists('poll_votes');
    }
}