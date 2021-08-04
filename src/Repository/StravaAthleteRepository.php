<?php

namespace App\Repository;

use App\Entity\StravaAthlete;
use App\Shared\StravaAPICalls;
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
                $responseData = StravaAPICalls::refreshAuthToken( $athlete );
                $athlete->setAuthToken($responseData->access_token);
                $athlete->setTokenExpiryTime( new \DateTime("@" . $responseData->expires_at));
                $athlete->setRefreshToken($responseData->refresh_token);
                $this->getEntityManager()->persist($athlete);
                $this->getEntityManager()->flush();
            }
            if(!$athlete->getAuthToken()){
                throw new \Exception("No auth token for athlete");
            }
            $response[] = StravaAPICalls::getActivities( $athlete );
        }
        return $response;
    }


}
