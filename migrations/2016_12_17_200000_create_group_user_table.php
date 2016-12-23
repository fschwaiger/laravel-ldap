<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupUserTable extends Migration
{
    /**
     * Adds a mapping table to create a many-to-many
     * connection between users and groups.
     */
    public function up()
    {
        Schema::create('group_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id');
            $table->integer('user_id');
        });   
    }

    /**
     * Reverts the above migration.
     */
    public function down()
    {
        Schema::dropIfExists('group_user');   
    }
}
