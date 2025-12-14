<?php

namespace App\Repository;

use App\Entity\BusinessWorkingHours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BusinessWorkingHours>
 */
class BusinessWorkingHoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessWorkingHours::class);
    }
}
