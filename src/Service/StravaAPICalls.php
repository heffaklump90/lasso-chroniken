<?php

namespace App\Service;

use App\Entity\StravaAthlete;
use App\Exception\AuthTokenExpiredException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaAPICalls
{

    private HttpClientInterface $httpClient;

    const STRAVA_API_TOKEN_URI = "https://www.strava.com/api/v3/oauth/token";
    const STRAVA_API_ACTIVITIES_URI = "https://www.strava.com/api/v3/athlete/activities";
    const STRAVA_API_ACTIVITY_URI = "https://www.strava.com/api/v3/activities/%d";
    const STRAVA_API_ATHLETE_URI = "https://www.strava.com/api/v3/athlete";

    const STRAVA_WEB_ACTIVITY_URI = "https://www.strava.com/activities/%d";

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getAuthCode(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('POST', self::STRAVA_API_TOKEN_URI, ['query' => [
            'client_id' => $stravaAthlete->getClientId(),
            'client_secret' => $stravaAthlete->getClientSecret(),
            'code' => $stravaAthlete->getAuthorizationCode(),
            'grant_type' => 'authorization_code',
        ]]);

        $data = json_decode( $response->getContent() );
        return $data;
    }

    public function refreshAuthToken(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('POST', self::STRAVA_API_TOKEN_URI,['query' => [
                'client_id' => $stravaAthlete->getClientId(),
                'client_secret' => $stravaAthlete->getClientSecret(),
                'grant_type' => 'refresh_token',
                'refresh_token' => $stravaAthlete->getRefreshToken()
            ]]);

        $data = json_decode($response->getContent());
        return $data;
    }

    public function getActivities(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request('GET', self::STRAVA_API_ACTIVITIES_URI, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );

        $data = json_decode($response->getContent());
        return $data;
    }

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

        $data = json_decode($response->getContent());
        return $data;
    }

    public function getAthleteData(StravaAthlete $stravaAthlete)
    {
        $response = $this->httpClient->request( 'GET', self::STRAVA_API_ATHLETE_URI, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );


        $data = json_decode($response->getContent());
        return $data;
    }
}