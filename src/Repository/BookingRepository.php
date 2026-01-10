<?php

namespace App\Repository;

use App\Entity\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findByStaffAndDateRange($staff, \DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.staff = :staff')
            ->andWhere('b.startsAt >= :start')
            ->andWhere('b.startsAt <= :end')
            ->andWhere('b.status != :cancelled')
            ->setParameter('staff', $staff)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('cancelled', 'cancelled')
            ->orderBy('b.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOverlappingBookings($staff, \DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.staff = :staff')
            ->andWhere('b.startsAt < :end')
            ->andWhere('b.endsAt > :start')
            ->andWhere('b.status != :cancelled')
            ->setParameter('staff', $staff)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('cancelled', 'cancelled')
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingByUser($user): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->andWhere('b.startsAt >= :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->orderBy('b.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPastByUser($user): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->andWhere('b.startsAt < :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->orderBy('b.startsAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByBusiness($business): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.business = :business')
            ->setParameter('business', $business)
            ->orderBy('b.startsAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
