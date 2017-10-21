<?php

namespace App\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserDemographicsTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            "gender" => $user->gender,
            "ethnicity" => $user->ethnicity,
        ];
    }
}
