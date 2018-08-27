<?php

require "bootstrap.php";

use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::schema()->create('defaultMessages', function ($table) {

    $table->increments('id');

    $table->string('textName')->unique();

    $table->string('textValue');

    $table->timestamps();

});