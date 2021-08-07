<?php

namespace App\Exception;

class AuthTokenExpiredException extends \Exception
{
    private $clientId;

    public function __construct(
        int $clientId = 0,
        $message = '',
        $code = 0,
        \Exception $previousException = null
    )
    {
        $this->clientId = $clientId;
        parent::__construct($message, $code, $previousException);
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }
}