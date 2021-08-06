<?php

namespace App\Shared;

use App\Entity\StravaAthlete;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaAPICalls
{

    private static ?HttpClientInterface $httpClient = null;

    const STRAVA_AUTH_URI = "https://www.strava.com/oauth/authorize";
    const STRAVA_GET_AUTH_TOKEN_URI = 'https://www.strava.com/oauth/token';
    const REFRESH_URL = "https://www.strava.com/api/v3/oauth/token";
    const ACTIVITIES_URL = "https://www.strava.com/api/v3/athlete/activities";
    const ATHLETE_URL = "https://www.strava.com/api/v3/athlete";


    private static function initHttpClient()
    {
        if( null === self::$httpClient ){
            self::$httpClient = HttpClient::create();
        }
    }

    public static function getAuthCode(StravaAthlete $stravaAthlete)
    {
        self::initHttpClient();
        $response = self::$httpClient->request('POST', self::REFRESH_URL, ['query' => [
            'client_id' => $stravaAthlete->getClientId(),
            'client_secret' => $stravaAthlete->getClientSecret(),
            'code' => $stravaAthlete->getAuthorizationCode(),
            'grant_type' => 'authorization_code',
        ]]);

        return json_decode( $response->getContent() );
    }

    public static function refreshAuthToken(StravaAthlete $stravaAthlete, EntityManager $entityManager)
    {
        self::initHttpClient();
        $response = self::$httpClient->request('POST', self::REFRESH_URL,['query' => [
                'client_id' => $stravaAthlete->getClientId(),
                'client_secret' => $stravaAthlete->getClientSecret(),
                'grant_type' => 'refresh_token',
                'refresh_token' => $stravaAthlete->getRefreshToken()
            ]]);
        $responseData = json_decode($response->getContent());
        $stravaAthlete->setAuthToken($responseData->access_token);
        $stravaAthlete->setTokenExpiryTime( new \DateTime("@" . $responseData->expires_at));
        $stravaAthlete->setRefreshToken($responseData->refresh_token);
        $entityManager->persist($stravaAthlete);
        $entityManager->flush();
        return $responseData;
    }

    public static function getActivities(StravaAthlete $stravaAthlete)
    {
        self::initHttpClient();
        $response = self::$httpClient->request('GET', self::ACTIVITIES_URL, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );
        return json_decode($response->getContent());
    }

    public static function getLatestActivity(StravaAthlete $stravaAthlete)
    {
        self::initHttpClient();
        $response = self::$httpClient->request('GET', self::ACTIVITIES_URL, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
                'query' => [
                    'per_page' => 1,
                ]
            ]
        );
        return json_decode($response->getContent())[0];
    }

    public static function getAthleteData(StravaAthlete $stravaAthlete, EntityManager $entityManager)
    {
        self::initHttpClient();
        $response = self::$httpClient->request( 'GET', self::ATHLETE_URL, [
                'auth_bearer' => $stravaAthlete->getAuthToken(),
            ]
        );
        $stravaData = json_decode($response->getContent());
        $stravaAthlete->setName($stravaData->firstname);
        $stravaAthlete->setProfile($stravaData->profile);
        $stravaAthlete->setProfileMedium($stravaData->profile_medium);
        $entityManager->persist($stravaAthlete);
        $entityManager->flush();
        return $stravaData;
    }
}