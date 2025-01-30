<?php

namespace App\Domain\DataTable\Repository;

use App\Domain\DataTable\Entity\DataView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DataView>
 */
class DataViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataView::class);
    }
}
