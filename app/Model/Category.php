<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table="category";

    function getCountry(){
        return $this->belongsTo('App\Model\Configure','country');
    }
    function getCompany(){
        return $this->belongsTo('App\Model\Configure','company');
    }
    function getFourm(){
        return $this->belongsTo('App\Model\Configure','fourm');
    }
    function sold(){
        return $this->hasMany('App\Model\TransactionCategory','category_id')->with('soldTransaction');
    }
}
