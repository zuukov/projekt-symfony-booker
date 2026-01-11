<?php

namespace App\Service;

use App\Repository\BusinessRepository;
use App\Repository\ServiceRepository;
use App\Repository\StaffRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SearchService
{
    private AsciiSlugger $slugger;

    public function __construct(
        private BusinessRepository $businessRepository,
        private ServiceRepository $serviceRepository,
        private StaffRepository $staffRepository
    ) {
        $this->slugger = new AsciiSlugger();
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
        $cityBusinesses = $this->searchBusinessesByCity($query);
        $businesses = $this->mergeUniqueBusinesses($businesses, $cityBusinesses);

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
            ->setParameter('query', '%' . mb_strtolower($query) . '%')
            ->setMaxResults(20);

        return $qb->getQuery()->getResult();
    }

    private function searchBusinessesByCity(string $query): array
    {
        $normalizedQuery = $this->normalizeCity($query);

        if (mb_strlen($normalizedQuery) < 3) {
            return [];
        }

        $qb = $this->businessRepository->createQueryBuilder('b');
        $qb->where('b.city IS NOT NULL')
        ->setMaxResults(500);

        $businesses = $qb->getQuery()->getResult();
        $matched = [];

        foreach ($businesses as $business) {
            $city = (string) $business->getCity();
            $normalizedCity = $this->normalizeCity($city);

            if ($normalizedCity !== '' && str_contains($normalizedCity, $normalizedQuery)) {
                $matched[] = $business;
            }
        }

        return array_slice($matched, 0, 20);
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
                    $qb->expr()->like('LOWER(c.categoryFullName)', ':query'),
                    $qb->expr()->like('LOWER(c.categoryFriendlyName)', ':query'),
                    $qb->expr()->like('LOWER(b.businessName)', ':query')
                )
            ))
            ->setParameter('query', '%' . mb_strtolower($query) . '%')
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
            ->setParameter('query', '%' . mb_strtolower($query) . '%')
            ->setMaxResults(20);

        return $qb->getQuery()->getResult();
    }

    private function normalizeCity(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $slug = $this->slugger->slug($value)->lower()->toString();
        $slug = preg_replace('/[^a-z0-9]+/i', '', $slug);

        return $slug ?? '';
    }

    private function mergeUniqueBusinesses(array $a, array $b): array
    {
        $byId = [];

        foreach ($a as $business) {
            $byId[$business->getId()] = $business;
        }
        foreach ($b as $business) {
            $byId[$business->getId()] = $business;
        }

        return array_values($byId);
    }
}
