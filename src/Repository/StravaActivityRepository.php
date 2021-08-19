<?php

namespace App\Repository;

use App\Entity\StravaActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StravaActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method StravaActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method StravaActivity[]    findAll()
 * @method StravaActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StravaActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StravaActivity::class);
    }

    // /**
    //  * @return StravaActivity[] Returns an array of StravaActivity objects
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
    public function findOneBySomeField($value): ?StravaActivity
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
