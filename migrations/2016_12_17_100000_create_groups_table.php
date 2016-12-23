<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Adds a table for LDAP groups, identified by DN.
     * Since groups can also be mailboxes, a nullable
     * email field is imported as well.
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('dn')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverts the above migration.
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
}
