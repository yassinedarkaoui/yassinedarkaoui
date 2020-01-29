<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NewsReport extends Model
{
    protected $table = 'news_report';

    function news(){
        return $this->hasOne('App\Model\News','id','news');
    }
    function user(){
        return $this->hasOne('App\User','id','user');
    }
}
