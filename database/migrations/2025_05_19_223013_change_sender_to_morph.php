<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']); // if foreign key exists
            $table->dropColumn('sender_id');
            $table->nullableMorphs('sender'); // adds sender_id and sender_type
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropMorphs('sender'); // drops sender_id and sender_type
            $table->unsignedBigInteger('sender_id')->after('id'); // add back sender_id
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade'); // re-add foreign key constraint
        });
    }
};
