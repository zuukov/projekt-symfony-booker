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

    public function findByStaffAndDate($staff, \DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('sto')
            ->andWhere('sto.staff = :staff')
            ->andWhere('sto.startsAt < :end')
            ->andWhere('sto.endsAt > :start')
            ->setParameter('staff', $staff)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingByStaff($staff): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('sto')
            ->andWhere('sto.staff = :staff')
            ->andWhere('sto.startsAt >= :now')
            ->setParameter('staff', $staff)
            ->setParameter('now', $now)
            ->orderBy('sto.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
