<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMessageReactionsTableWithSenderType extends Migration
{
    public function up()
    {
        Schema::table('message_reactions', function (Blueprint $table) {
            // Modify user_id type (using raw SQL since Laravel doesn't support altering column types easily)
            $table->unsignedBigInteger('user_id')->change();

            // Add user_type column
            $table->string('user_type')->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('message_reactions', function (Blueprint $table) {
            // Remove user_type column on rollback
            $table->dropColumn('user_type');
            
            // **NOTE:** Laravel doesn't support reverting column type changes, 
            // so ensure backups exist before rolling back
        });
    }
}