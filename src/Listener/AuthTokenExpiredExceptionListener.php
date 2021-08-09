<?php

namespace App\Listener;

use App\Exception\AuthTokenExpiredException;
use App\Service\StravaAPICalls;
use App\Service\StravaDataPersistence;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class AuthTokenExpiredExceptionListener
{
    private StravaAPICalls $stravaAPICalls;
    private StravaDataPersistence $stravaDataPersistence;

    public function __construct(StravaAPICalls $stravaAPICalls,
                                StravaDataPersistence $stravaDataPersistence,
    )
    {
        $this->stravaAPICalls = $stravaAPICalls;
        $this->stravaDataPersistence = $stravaDataPersistence;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if( $exception instanceof AuthTokenExpiredException ){
            $data = $this->stravaAPICalls->refreshAuthToken($exception->getStravaAthlete());
            $this->stravaDataPersistence->saveRefreshTokenData($exception->getStravaAthlete(), $data);
        }
    }
}