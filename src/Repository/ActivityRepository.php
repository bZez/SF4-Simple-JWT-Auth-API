<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function findCalls($partner,$method)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.partner = :partner')
            ->andWhere('a.method = :method')
            ->setParameter('partner', $partner)
            ->setParameter('method', $method)
            ->orderBy('a.id', 'ASC')
            ->select('SUM(a.count) as count')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findCallsYear($partner,$method,$year)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.partner = :partner')
            ->andWhere('a.method = :method')
            ->andWhere('YEAR(a.date) = :year')
            ->setParameter('partner', $partner)
            ->setParameter('method', $method)
            ->setParameter('year', $year)
            ->orderBy('a.id', 'ASC')
            ->select('SUM(a.count) as count')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findCallsMonth($partner,$method,$month)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.partner = :partner')
            ->andWhere('a.method = :method')
            ->andWhere('MONTH(a.date) = :month')
            ->setParameter('partner', $partner)
            ->setParameter('method', $method)
            ->setParameter('month', $month)
            ->orderBy('a.id', 'ASC')
            ->select('SUM(a.count) as count')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    // /**
    //  * @return Activity[] Returns an array of Activity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Activity
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
