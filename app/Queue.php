<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    //
    protected $table = "queues";
    protected $fillable = ['_index','_type','_id','_score','_source','sort'];
    public $timestamps = false;
}
