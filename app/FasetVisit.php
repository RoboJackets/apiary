<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class FasetVisit extends Model
{
    use Notifiable, SoftDeletes;

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
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function addFasetResponse($faset_survey_id, $response)
    {
        $this->fasetResponses()->create(compact('faset_survey_id', 'response'));
    }

    public function save(array $options = array())
    {
        if (empty($this->visit_token)) {
            // Store 20 char secure random token
            $this->visit_token = strtr(base64_encode(random_bytes(15)), "+/=", "-_.");
        }
        return parent::save($options);
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->faset_email;
    }

    protected $fillable = ['faset_email', 'faset_name'];
}
