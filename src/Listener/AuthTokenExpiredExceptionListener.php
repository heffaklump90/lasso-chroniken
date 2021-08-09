<?php

namespace App\Listener;

use App\Exception\AuthTokenExpiredException;
use App\Service\StravaAPICalls;
use App\Service\StravaDataPersistence;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class AuthTokenExpiredExceptionListener
{
    private StravaAPICalls $stravaAPICalls;
    private StravaDataPersistence $stravaDataPersistence;
    private RequestStack $requestStack;
    private LoggerInterface $logger;

    public function __construct(StravaAPICalls $stravaAPICalls,
                                StravaDataPersistence $stravaDataPersistence,
                                RequestStack $requestStack,
                                LoggerInterface $logger
    )
    {
        $this->stravaAPICalls = $stravaAPICalls;
        $this->stravaDataPersistence = $stravaDataPersistence;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if( $exception instanceof AuthTokenExpiredException ){
            $data = $this->stravaAPICalls->refreshAuthToken($exception->getStravaAthlete());
            $this->stravaDataPersistence->saveRefreshTokenData($exception->getStravaAthlete(), $data);

            $request = $this->requestStack->getCurrentRequest();
            $response = $request->getRequestUri();
            $this->logger->log(LogLevel::INFO, "trying request again: " . $response);
            $event->setResponse($response);
        }
    }
}