<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table="cart";

    function category(){
        return $this->hasMany('App\Model\Category','category_id');
    }
}
