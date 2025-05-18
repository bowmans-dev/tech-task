<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateProfilePictureDefaultOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update any rows where profile_picture is null
        DB::table('users')->whereNull('profile_picture')->update([
            'profile_picture' => 'default_profile_image.webp'
        ]);

        // Now change the column default using change()
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_picture')
                  ->default('default_profile_image.webp')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert the default if needed; for example, set it to null.
            $table->string('profile_picture')->default(null)->change();
        });
    }
}