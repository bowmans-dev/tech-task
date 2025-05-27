<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageReactionsTable extends Migration
{
    public function up()
    {
        Schema::create('message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('emoji', 20);
            $table->timestamps();

            $table->unique(['message_id', 'user_id']); // Ensures one reaction per user per message
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_reactions');
    }
}