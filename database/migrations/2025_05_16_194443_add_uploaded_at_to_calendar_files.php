<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calendar_files', function (Blueprint $table) {
            $table->timestamp('uploaded_at')->nullable()->after('file_path');
        });
    }

    public function down()
    {
        Schema::table('calendar_files', function (Blueprint $table) {
            $table->dropColumn('uploaded_at');
        });
    }
};
