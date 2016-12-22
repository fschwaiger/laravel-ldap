<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->string('ldap_guid')->unique();
            $table->string('ldap_dn');
            $table->string('email')->nullable();
            $table->dropColumn('password');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique();
            $table->string('password');
            $table->dropColumn('ldap_guid');
            $table->dropColumn('ldap_dn');
        });
    }
}
