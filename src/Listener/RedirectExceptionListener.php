<?php

namespace App\Listener;

use App\Exception\RedirectException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class RedirectExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if( $exception instanceof RedirectException ){
            $event->setResponse($exception->getRedirectResponse());
        }
    }
}