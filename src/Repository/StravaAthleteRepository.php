<?php

namespace App\Repository;

use App\Entity\StravaAthlete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    // /**
    //  * @return StravaAthlete[] Returns an array of StravaAthlete objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StravaAthlete
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
