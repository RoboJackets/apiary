<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitingVisit extends Model
{
    use Notifiable, SoftDeletes;

    /**
     *  Get the Recruiting Responses associated with this Recruiting Visit.
     */
    public function recruitingResponses()
    {
        return $this->hasMany(\App\RecruitingResponse::class);
    }

    /**
     *  Get the organization member who visited at the recruiting event, assuming the record could be linked.
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function save(array $options = [])
    {
        if (empty($this->visit_token)) {
            // Store 20 char secure random token
            $this->visit_token = strtr(base64_encode(random_bytes(15)), '+/=', '-_.');
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
        return $this->recruiting_email;
    }

    protected $fillable = ['recruiting_email', 'recruiting_name'];
}
