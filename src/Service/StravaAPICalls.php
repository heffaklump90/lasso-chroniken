<?php

namespace App\Service;

use App\Entity\StravaAthlete;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaAPICalls
{

    private HttpClientInterface $httpClient;

    const STRAVA_TOKEN_URI = "https://www.strava.com/api/v3/oauth/token";
    const STRAVA_ACTIVITIES_URL = "https://www.strava.com/api/v3/athlete/activities";
    const STRAVA_ATHLETE_URL = "https://www.strava.com/api/v3/athlete";

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getAuthCode(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('POST', self::STRAVA_TOKEN_URI, ['query' => [
            'client_id' => $stravaAthlete->getClientId(),
            'client_secret' => $stravaAthlete->getClientSecret(),
            'code' => $stravaAthlete->getAuthorizationCode(),
            'grant_type' => 'authorization_code',
        ]]);

        return json_decode( $response->getContent() );
    }

    public function refreshAuthToken(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('POST', self::STRAVA_TOKEN_URI,['query' => [
                'client_id' => $stravaAthlete->getClientId(),
                'client_secret' => $stravaAthlete->getClientSecret(),
                'grant_type' => 'refresh_token',
                'refresh_token' => $stravaAthlete->getRefreshToken()
            ]]);

        return json_decode($response->getContent());;
    }

    public function getActivities(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('GET', self::STRAVA_ACTIVITIES_URL, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );
        return json_decode($response->getContent());
    }

    public function getLatestActivity(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('GET', self::STRAVA_ACTIVITIES_URL, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
                'query' => [
                    'per_page' => 1,
                ]
            ]
        );

        return json_decode($response->getContent())[0];
    }

    public function getAthleteData(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request( 'GET', self::STRAVA_ATHLETE_URL, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );

        return json_decode($response->getContent());
    }
}