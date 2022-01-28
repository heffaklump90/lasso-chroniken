<?php

namespace App\Service;

use App\Entity\StravaAthlete;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaAPICalls
{

    private HttpClientInterface $httpClient;
    private StravaDataPersistence $stravaDataPersistence;
    private LoggerInterface $logger;

    const STRAVA_API_TOKEN_URI = "https://www.strava.com/api/v3/oauth/token";
    const STRAVA_API_ACTIVITIES_URI = "https://www.strava.com/api/v3/athlete/activities";
    const STRAVA_API_ACTIVITY_URI = "https://www.strava.com/api/v3/activities/%d";
    const STRAVA_API_ATHLETE_URI = "https://www.strava.com/api/v3/athlete";
    const STRAVA_API_LIST_ACTIVITIES_URI = "https://www.strava.com/api/v3/athlete/activities";

    const STRAVA_WEB_ACTIVITY_URI = "https://www.strava.com/activities/%d";

    /**
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient, StravaDataPersistence  $stravaDataPersistence, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->stravaDataPersistence = $stravaDataPersistence;
        $this->logger = $logger;
    }

    /**
     * @param StravaAthlete $stravaAthlete
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getAuthCode(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('POST', self::STRAVA_API_TOKEN_URI, ['query' => [
            'client_id' => $stravaAthlete->getClientId(),
            'client_secret' => $stravaAthlete->getClientSecret(),
            'code' => $stravaAthlete->getAuthorizationCode(),
            'grant_type' => 'authorization_code',
        ]]);

        return json_decode( $response->getContent() );
    }

    /**
     * @param StravaAthlete $stravaAthlete
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function refreshAuthToken(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('POST', self::STRAVA_API_TOKEN_URI,['query' => [
                'client_id' => $stravaAthlete->getClientId(),
                'client_secret' => $stravaAthlete->getClientSecret(),
                'grant_type' => 'refresh_token',
                'refresh_token' => $stravaAthlete->getRefreshToken()
            ]]);

        return json_decode($response->getContent());
    }

    /**
     * @param StravaAthlete $stravaAthlete
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getActivities(StravaAthlete $stravaAthlete)
    {
        $parameters = [
            'auth_bearer' => $stravaAthlete->getAuthToken(),
        ];
        return $this->_executeStravaCall( $stravaAthlete, self::STRAVA_API_ACTIVITIES_URI, $parameters);
    }

    /**
     * @param StravaAthlete $stravaAthlete
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getLatestActivity(StravaAthlete $stravaAthlete)
    {
        $parameters = [
            'auth_bearer' => $stravaAthlete->getAuthToken(),
            'query' => [
                'per_page' => 1,
            ]
        ];
        $data = $this->_executeStravaCall( $stravaAthlete, self::STRAVA_API_ACTIVITIES_URI, $parameters)[0];
        return $this->getActivityDetail($stravaAthlete, $data->id);
    }

    /**
     * @param StravaAthlete $stravaAthlete
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getAthleteData(StravaAthlete $stravaAthlete)
    {
        $parameters =  [
            'auth_bearer' => $stravaAthlete->getAuthToken(),
        ];
        return $this->_executeStravaCall( $stravaAthlete, self::STRAVA_API_ATHLETE_URI, $parameters);
    }

    /**
     * @param StravaAthlete $stravaAthlete
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getActivityList(StravaAthlete $stravaAthlete)
    {
        $parameters = ['auth_bearer' => $stravaAthlete->getAuthToken()];
        return $this->_executeStravaCall( $stravaAthlete, self::STRAVA_API_LIST_ACTIVITIES_URI, $parameters);
    }

    /**
     * @param StravaAthlete $stravaAthlete
     * @param int $activityId
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getActivityDetail(StravaAthlete $stravaAthlete, int $activityId)
    {
        $requestUri = sprintf(self::STRAVA_API_ACTIVITY_URI, $activityId);
        $parameters = [
            'auth_bearer' => $stravaAthlete->getAuthToken(),
            'query' => [
                'include_all_efforts' => false,
            ]
        ];
        return $this->_executeStravaCall($stravaAthlete, $requestUri, $parameters);
    }


    private function _executeStravaCall($athlete, $request, $parameters)
    {
        try {
            $response = $this->httpClient->request("GET", $request, $parameters);
            return json_decode($response->getContent());
        } catch (ClientException $exception) {
            if ($exception->getCode() == Response::HTTP_UNAUTHORIZED) {
                $refreshTokenData = $this->refreshAuthToken($athlete);
                $this->stravaDataPersistence->saveRefreshTokenData($athlete, $refreshTokenData);
            } else {
                throw $exception;
            }
            $response = $this->httpClient->request("GET", $request, $parameters);
            return json_decode($response->getContent());
        }
    }
}