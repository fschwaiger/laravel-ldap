<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendUsersTable extends Migration
{
    /**
     * Adapts the users table to the required layout for the extension
     * to work. This assumes that only the initial migration named
     * 2014_10_12_000000_create_users_table creates the user table.
     *
     * The final layout needs to be at least:
     *
     *  - id -> increments
     *  - username -> string -> unique
     *  - email -> string -> nullable
     *  - remember_token -> string -> nullable
     *  - created_at -> timestamp
     *  - updated_at -> timestamp
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->dropColumn('password');
        });
    }

    /**
     * Reverts the above migration.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password');
            $table->dropColumn('username');
        });
    }
}
