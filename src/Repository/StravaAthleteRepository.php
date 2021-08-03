<?php

namespace App\Repository;

use App\Entity\StravaAthlete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @method StravaAthlete|null find($id, $lockMode = null, $lockVersion = null)
 * @method StravaAthlete|null findOneBy(array $criteria, array $orderBy = null)
 * @method StravaAthlete[]    findAll()
 * @method StravaAthlete[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StravaAthleteRepository extends ServiceEntityRepository
{
    const REFRESH_URL = "https://www.strava.com/api/v3/oauth/token";
    const ACTIVITIES_URL = "https://www.strava.com/api/v3/athlete/activities";
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StravaAthlete::class);
    }

    public function findLatestActivityForAll(): Collection
    {
        $athletes = $this->findAll();
        $httpClient = HttpClient::create();
        $response = new ArrayCollection();
        foreach($athletes as $athlete){
            //refresh token?
            if($athlete->getTokenExpiryTime() < new \DateTime()){
                $stravaResponse = $httpClient->request(GET, self::REFRESH_URL,['query' => [
                    'client_id' => $athlete->getClientId(),
                    'client_secret' => $athlete->getClientSecret(),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $athlete->getRefreshToken()
                ]]);
                $responseData = json_decode($stravaResponse->getContent());
                $athlete->setAuthToken($responseData->access_token);
                $athlete->setTokenExpiryTime("@" . $responseData->expires_at);
                $athlete->setRefreshToken($responseData->refresh_token);
                $this->getEntityManager()->persist($athlete);
                $this->getEntityManager()->flush();
            }
            if(!$athlete->getAuthToken()){
                throw new \Exception("No auth token for athlete");
            }
            //get the activities
            $stravaResponse = $httpClient->request('GET', self::ACTIVITIES_URL, [
                'auth_bearer' => $athlete->getAuthToken(),
            ]);
            $response[] = json_decode($stravaResponse->getContent());
        }
        return $response;
    }


}
