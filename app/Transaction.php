<?php

namespace Nexos;

use Illuminate\Database\Eloquent\Model;

class transaction extends Model
{
    public function account(){
        return $this->belongsTo('Nexos\Account');
    }

    public function user(){
        return $this->belongsTo('Nexos\User');
    }
}
