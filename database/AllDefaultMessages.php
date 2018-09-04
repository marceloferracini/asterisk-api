<?php

require "bootstrap.php";

use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::schema()->create('all_default_messages', function ($table) {

    $table->increments('id');

    $table->string('textName')->unique();

    $table->string('textValue')->nullable();

    $table->timestamps();

});