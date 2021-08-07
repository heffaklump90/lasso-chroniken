<?php

namespace App\Listener;

use App\Exception\AuthTokenExpiredException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class AuthTokenExpiredExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if( $exception instanceof AuthTokenExpiredException ){
            // TODO: call the api to get a refresh token and save the token data?
        }
    }
}