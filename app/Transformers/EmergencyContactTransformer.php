<?php

namespace App\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class EmergencyContactTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            "name" => $user->emergency_contact_name,
            "phone" => $user->emergency_contact_phone
        ];
    }
}
