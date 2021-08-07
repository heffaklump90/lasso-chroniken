<?php

namespace App\Service;

use App\Entity\StravaAthlete;
use Doctrine\ORM\EntityManagerInterface;

class StravaDataPersistence
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveAuthData(StravaAthlete $athlete, $authData)
    {
        $athlete->setAuthToken($authData->access_token);
        $athlete->setRefreshToken($authData->refresh_token);
        $athlete->setTokenExpiryTime( new \DateTime("@" . $authData->expires_at) );
        $this->entityManager->persist($athlete);
        $this->entityManager->flush();
    }

    public function saveAthleteData(StravaAthlete $athlete, $athleteData)
    {
        $athlete->setName($athleteData->firstname);
        $athlete->setProfile($athleteData->profile);
        $athlete->setProfileMedium($athleteData->profile_medium);
        $this->entityManager->persist($athlete);
        $this->entityManager->flush();
    }

    public function saveLatestActivityData(StravaAthlete $stravaAthlete, $latestActivityData)
    {
        $stravaAthlete->setLatestActivityId($latestActivityData->id);
        $stravaAthlete->setLatestActivityName($latestActivityData->name);
        $this->entityManager->persist($stravaAthlete);
        $this->entityManager->flush();
    }

    public function saveRefreshTokenData(StravaAthlete $stravaAthlete, $refreshTokenData )
    {
        $stravaAthlete->setAuthToken($refreshTokenData->access_token);
        $stravaAthlete->setTokenExpiryTime( new \DateTime("@" . $refreshTokenData->expires_at));
        $stravaAthlete->setRefreshToken($refreshTokenData->refresh_token);
        $this->entityManager->persist($stravaAthlete);
        $this->entityManager->flush();
    }
}