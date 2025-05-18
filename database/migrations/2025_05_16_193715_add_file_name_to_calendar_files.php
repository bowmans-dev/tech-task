<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calendar_files', function (Blueprint $table) {
            $table->string('file_name')->after('calendar_id'); 
        });
    }

    public function down()
    {
        Schema::table('calendar_files', function (Blueprint $table) {
            $table->dropColumn('file_name');
        });
    }
};
