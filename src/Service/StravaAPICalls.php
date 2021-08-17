<?php

namespace App\Service;

use App\Entity\StravaAthlete;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaAPICalls
{

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    const STRAVA_API_TOKEN_URI = "https://www.strava.com/api/v3/oauth/token";
    const STRAVA_API_ACTIVITIES_URI = "https://www.strava.com/api/v3/athlete/activities";
    const STRAVA_API_ACTIVITY_URI = "https://www.strava.com/api/v3/activities/%d";
    const STRAVA_API_ATHLETE_URI = "https://www.strava.com/api/v3/athlete";

    const STRAVA_WEB_ACTIVITY_URI = "https://www.strava.com/activities/%d";

    /**
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
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
        $response = $this->httpClient->request('GET', self::STRAVA_API_ACTIVITIES_URI, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );

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
    public function getLatestActivity(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('GET', self::STRAVA_API_ACTIVITIES_URI, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
                'query' => [
                    'per_page' => 1,
                ]
            ]
        );

        $data = json_decode($response->getContent())[0];
        $response = $this->httpClient->request('GET', sprintf(self::STRAVA_API_ACTIVITY_URI, $data->id), [
            'auth_bearer' => $stravaAthlete->getAuthToken(),
            'query' => [
                'include_all_efforts' => false,
            ]
        ]);
        $response = json_decode($response->getContent());
        $this->logger->log(LogLevel::INFO, "map polyline: " . $response->map->polyline);
        return $response;
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
        $response = $this->httpClient->request( 'GET', self::STRAVA_API_ATHLETE_URI, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );


        return json_decode($response->getContent());
    }
}