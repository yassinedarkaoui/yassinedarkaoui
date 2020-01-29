<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
   protected $table="news";

   function comments(){
       return $this->hasMany('App\Model\NewsComment','news_id')->orderBy('created_at','desc');
   }
}
