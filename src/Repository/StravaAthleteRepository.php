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




}
