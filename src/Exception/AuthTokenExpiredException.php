<?php

namespace App\Exception;

use App\Entity\StravaAthlete;

class AuthTokenExpiredException extends \Exception
{
    private StravaAthlete $stravaAthlete;

    public function __construct(
        StravaAthlete $stravaAthlete = null,
        $message = '',
        $code = 0,
        \Exception $previousException = null
    )
    {
        $this->stravaAthlete = $stravaAthlete;
        parent::__construct($message, $code, $previousException);
    }

    public function getStravaAthlete(): StravaAthlete
    {
        return $this->stravaAthlete;
    }
}