<?php

namespace App\Repository;

use App\Entity\StaffTimeOff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StaffTimeOff>
 */
class StaffTimeOffRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaffTimeOff::class);
    }
}
