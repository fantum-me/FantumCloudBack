<?php

namespace App\Domain\StorageItem;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StorageItem>
 *
 * @method StorageItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method StorageItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method StorageItem[]    findAll()
 * @method StorageItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StorageItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StorageItem::class);
    }

//    /**
//     * @return StorageItem[] Returns an array of StorageItem objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StorageItem
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
