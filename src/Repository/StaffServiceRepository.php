<?php

namespace App\Repository;

use App\Entity\StaffService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StaffService>
 */
class StaffServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaffService::class);
    }

    public function findServicesByStaff($staff): array
    {
        return $this->createQueryBuilder('ss')
            ->andWhere('ss.staff = :staff')
            ->setParameter('staff', $staff)
            ->getQuery()
            ->getResult();
    }
}
