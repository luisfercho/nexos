<?php

namespace Nexos;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public function accounts(){
        return $this->hasMany('Nexos\Account');
    }
}
