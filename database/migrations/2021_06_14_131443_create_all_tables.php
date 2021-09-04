<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->default('default.png');
            $table->string('email')->unique();
            $table->string('password');
        });
        Schema::create('userfavorites', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->integer('id_photographer');

        });
        Schema::create('userappointments', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->integer('id_photographer');
            $table->datetime('ap_datetime');
        });

        Schema::create('photographers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->default('default.png');
            $table->float('stars')->default(0);
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
        });
        Schema::create('photographerphotos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_photographer');
            $table->string('url');
        });
        Schema::create('photographerreviews', function (Blueprint $table) {
            $table->id();
            $table->integer('id_photographer');
            $table->float('rate');
        });
        Schema::create('photographerservices', function (Blueprint $table) {
            $table->id();
            $table->integer('id_photographer');
            $table->string('name');
            $table->float('price');
        });
        Schema::create('photographertestimonials', function (Blueprint $table) {
            $table->id();
            $table->integer('id_photographer');
            $table->string('name');
            $table->float('rate');
            $table->string('body');
        });
        Schema::create('photographeravailability', function (Blueprint $table) {
            $table->id();
            $table->integer('id_photographer');
            $table->integer('weekday');
            $table->text('hours');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('userfavorites');
        Schema::dropIfExists('userappointments');
        Schema::dropIfExists('photographers');
        Schema::dropIfExists('photographerphotos');
        Schema::dropIfExists('photographerreviews');
        Schema::dropIfExists('photographerservices');
        Schema::dropIfExists('photographertestimonials');
        Schema::dropIfExists('photographeravailability');
    }
}
