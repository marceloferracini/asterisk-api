<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class AllDefaultMessages extends Eloquent

{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [

        'textName', 'textValue'

    ];

    protected $table = "all_default_messages";
}