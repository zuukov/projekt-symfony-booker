<?php

namespace App\Service;

use App\Repository\BusinessRepository;
use App\Repository\ServiceRepository;
use App\Repository\StaffRepository;

class SearchService
{
    public function __construct(
        private BusinessRepository $businessRepository,
        private ServiceRepository $serviceRepository,
        private StaffRepository $staffRepository
    ) {
    }

    public function search(string $query): array
    {
        $query = trim($query);

        if (empty($query)) {
            return [
                'businesses' => [],
                'services' => [],
                'staff' => [],
                'total' => 0
            ];
        }

        $businesses = $this->searchBusinesses($query);
        $services = $this->searchServices($query);
        $staff = $this->searchStaff($query);

        return [
            'businesses' => $businesses,
            'services' => $services,
            'staff' => $staff,
            'total' => count($businesses) + count($services) + count($staff)
        ];
    }

    private function searchBusinesses(string $query): array
    {
        $qb = $this->businessRepository->createQueryBuilder('b');

        $qb->where($qb->expr()->orX(
            $qb->expr()->like('LOWER(b.businessName)', ':query'),
            $qb->expr()->like('LOWER(b.description)', ':query'),
            $qb->expr()->like('LOWER(b.city)', ':query')
        ))
        ->setParameter('query', '%' . strtolower($query) . '%')
        ->setMaxResults(20);

        return $qb->getQuery()->getResult();
    }

    private function searchServices(string $query): array
    {
        $qb = $this->serviceRepository->createQueryBuilder('s');

        $qb->leftJoin('s.business', 'b')
           ->leftJoin('s.category', 'c')
           ->where($qb->expr()->andX(
                $qb->expr()->eq('s.isActive', ':active'),
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(s.name)', ':query'),
                    $qb->expr()->like('LOWER(s.description)', ':query'),
                    $qb->expr()->like('LOWER(c.categoryFriendlyName)', ':query'),
                    $qb->expr()->like('LOWER(b.businessName)', ':query')
                )
           ))
           ->setParameter('query', '%' . strtolower($query) . '%')
           ->setParameter('active', true)
           ->setMaxResults(20);

        return $qb->getQuery()->getResult();
    }

    private function searchStaff(string $query): array
    {
        $qb = $this->staffRepository->createQueryBuilder('st');

        $qb->leftJoin('st.business', 'b')
           ->where($qb->expr()->orX(
                $qb->expr()->like('LOWER(st.name)', ':query'),
                $qb->expr()->like('LOWER(st.surname)', ':query'),
                $qb->expr()->like('LOWER(st.aboutMe)', ':query'),
                $qb->expr()->like('LOWER(b.businessName)', ':query')
           ))
           ->setParameter('query', '%' . strtolower($query) . '%')
           ->setMaxResults(20);

        return $qb->getQuery()->getResult();
    }
}
