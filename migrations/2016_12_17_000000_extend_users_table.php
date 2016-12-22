<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->string('guid')->unique();
            $table->string('dn');
            $table->string('email')->nullable();
            $table->timestamp('imported_at');
            $table->dropColumn('password');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique();
            $table->string('password');
            $table->dropColumn('imported_at');
            $table->dropColumn('guid');
            $table->dropColumn('dn');
        });
    }
}
