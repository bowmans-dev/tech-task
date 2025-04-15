<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserDetailsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('gender')->nullable();
            $table->string('profile_picture')->nullable();

            // Remove the redundant 'name' column
            $table->dropColumn('name');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'country',
                'gender',
                'profile_picture',
            ]);

            // Restore the 'name' column
            $table->string('name')->nullable();
        });
    }
}
