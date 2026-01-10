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

    public function findByStaffAndWeekday($staff, int $weekday): array
    {
        return $this->createQueryBuilder('swh')
            ->andWhere('swh.staff = :staff')
            ->andWhere('swh.weekday = :weekday')
            ->setParameter('staff', $staff)
            ->setParameter('weekday', $weekday)
            ->orderBy('swh.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
