<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CategoryReport extends Model
{
    protected $table = 'category_report';
    function reCategory(){
        return $this->hasOne('App\Model\Category','id','category');
    }
    function user(){
        return $this->hasOne('App\User','id','user');
    }
}
