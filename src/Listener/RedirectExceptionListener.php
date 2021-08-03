<?php

namespace App\Listener;

use App\Exception\RedirectException;
use PhpParser\Node\Expr\Instanceof_;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Contracts\EventDispatcher\Event;

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