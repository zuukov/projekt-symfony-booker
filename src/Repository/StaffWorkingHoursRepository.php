<?php

namespace App\Repository;

use App\Entity\StaffWorkingHours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StaffWorkingHours>
 */
class StaffWorkingHoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaffWorkingHours::class);
    }
}
