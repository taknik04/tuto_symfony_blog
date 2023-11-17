<?php

namespace App\Repository;

use App\Entity\VerifiedAt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VerifiedAt>
 *
 * @method VerifiedAt|null find($id, $lockMode = null, $lockVersion = null)
 * @method VerifiedAt|null findOneBy(array $criteria, array $orderBy = null)
 * @method VerifiedAt[]    findAll()
 * @method VerifiedAt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VerifiedAtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VerifiedAt::class);
    }

//    /**
//     * @return VerifiedAt[] Returns an array of VerifiedAt objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?VerifiedAt
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
