<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FasetVisit extends Model
{
    /**
     *  Get the FASET Responses associated with this FASET Visit
     */
    public function fasetResponses()
    {
        return $this->hasMany('App\FasetResponse');
    }

    /**
     *  Get the organization member who visited at FASET, assuming the record could be linked
     */
    public function users()
    {
        return $this->belongsTo('App\User');
    }
}
