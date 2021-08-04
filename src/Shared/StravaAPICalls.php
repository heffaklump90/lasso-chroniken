<?php

namespace App\Shared;

use App\Entity\StravaAthlete;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaAPICalls
{

    private static ?HttpClientInterface $httpClient = null;

    const STRAVA_AUTH_URI = "https://www.strava.com/oauth/authorize";
    const STRAVA_GET_AUTH_TOKEN_URI = 'https://www.strava.com/oauth/token';
    const REFRESH_URL = "https://www.strava.com/oauth/token";
    const ACTIVITIES_URL = "https://www.strava.com/api/v3/athlete/activities";
    const ATHLETE_URL = "https://www.strava.com/api/v3/athlete";


    private static function initHttpClient(): HttpClientInterface
    {
        if( null === self::$httpClient ){
            self::$httpClient = HttpClient::create();
        }
        return self::$httpClient;
    }

    public static function getAuthCode(StravaAthlete $stravaAthlete)
    {

        $response = self::initHttpClient()->request('POST', self::STRAVA_GET_AUTH_TOKEN_URI, ['query' => [
            'client_id' => $stravaAthlete->getClientId(),
            'client_secret' => $stravaAthlete->getClientSecret(),
            'code' => $stravaAthlete->getAuthorizationCode(),
            'grant_type' => 'authorization_code',
        ]]);

        return json_decode( $response->getContent() );
    }

    public static function refreshAuthToken(StravaAthlete $stravaAthlete)
    {
        $response = self::initHttpClient()->request('POST', self::REFRESH_URL,['query' => [
                'client_id' => $stravaAthlete->getClientId(),
                'client_secret' => $stravaAthlete->getClientSecret(),
                'grant_type' => 'refresh_token',
                'refresh_token' => $stravaAthlete->getRefreshToken()
            ]]);

        return json_decode($response->getContent());
    }

    public static function getActivities(StravaAthlete $stravaAthlete)
    {
        $response = self::initHttpClient()->request('GET', self::ACTIVITIES_URL, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );
        return json_decode($response->getContent());
    }

    public static function getLatestActivity(StravaAthlete $stravaAthlete)
    {
        $response = self::initHttpClient()->request('GET', self::ACTIVITIES_URL, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
                'query' => [
                    'per_page' => 1,
                ]
            ]
        );
        return json_decode($response->getContent());
    }

    public static function getAthleteData(StravaAthlete $stravaAthlete)
    {
        $response = self::initHttpClient()->request( 'GET', self::ATHLETE_URL, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );
        return json_decode($response->getContent());
    }
}