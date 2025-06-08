<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskCompletionsTable extends Migration
{
    public function up()
    {
        Schema::create('task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->morphs('worker'); // worker_id and worker_type (User/Admin)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_completions');
    }
}