<?php

namespace App\Domain\AccessControl;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccessControl>
 *
 * @method AccessControl|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessControl|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccessControl[]    findAll()
 * @method AccessControl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessControlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessControl::class);
    }

//    /**
//     * @return AccessControl[] Returns an array of AccessControl objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AccessControl
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
