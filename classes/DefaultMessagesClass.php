<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class DefaultMessagesClass extends Eloquent

{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [

        'textName', 'textvalue'

    ];
}