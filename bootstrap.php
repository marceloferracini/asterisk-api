<?php
//https://medium.com/@kshitij206/use-eloquent-without-laravel-7e1c73d79977
require "vendor/autoload.php";

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

//load .env file
//$dotenv = new Dotenv\Dotenv(__DIR__ );
//$dotenv->load();

$capsule->addConnection([

    "driver" => getenv("dbDriver"),

    "host" => getenv("dbHost"),

    "database" => getenv("dbDatabase"),

    "username" => getenv("dbUserName"),

    "password" => getenv("dbPassword")

]);

//Make this Capsule instance available globally.
$capsule->setAsGlobal();

// Setup the Eloquent ORM.
$capsule->bootEloquent();
$capsule->bootEloquent();