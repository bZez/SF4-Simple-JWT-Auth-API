<?php

namespace App\Repository;

use App\Entity\AccessRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AccessRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccessRequest[]    findAll()
 * @method AccessRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessRequest::class);
    }

    // /**
    //  * @return AccessRequest[] Returns an array of AccessRequest objects
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
    public function findOneBySomeField($value): ?AccessRequest
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
