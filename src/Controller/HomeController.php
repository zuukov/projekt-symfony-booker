<?php

namespace App\Controller;

use App\Entity\Business;
use App\Repository\BusinessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class HomeController extends AbstractController
{
    public function __construct(
        private BusinessRepository $businessRepository,
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em): Response
    {
        $allIds = $em->createQueryBuilder()
            ->select('b.id')
            ->from(Business::class, 'b')
            ->getQuery()
            ->getSingleColumnResult();

        shuffle($allIds);
        $ids = array_slice($allIds, 0, 7);

        $featured = [];

        if (!empty($ids)) {
            $rows = $em->createQueryBuilder()
                ->select('b AS business', 'COALESCE(AVG(r.rating), 0) AS avgRating', 'COUNT(r.id) AS reviewCount')
                ->from(Business::class, 'b')
                ->leftJoin('b.reviews', 'r')
                ->where('b.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->groupBy('b.id')
                ->getQuery()
                ->getResult();

            $byId = [];

            foreach ($rows as $row) {
                $b = $row['business'];
                $byId[$b->getId()] = $row;
            }

            foreach ($ids as $id) {

                if (!isset($byId[$id])) {
                    continue;
                }

                $b = $byId[$id]['business'];

                $avgRating = (float) $byId[$id]['avgRating'];
                $reviewCount = (int) $byId[$id]['reviewCount'];

                $name = $b->getBusinessName();
                $name = is_string($name) ? $name : '';

                $address = $b->getAddress();
                $address = is_string($address) ? $address : '';

                $city = $b->getCity();
                $city = is_string($city) ? $city : '';

                $postal = $b->getPostalCode();
                $postal = is_string($postal) ? $postal : '';

                $metaParts = [];
                if ($postal !== '' || $city !== '') {
                    $metaParts[] = trim($postal . ' ' . $city);
                }

                $addressLine = $address;
                if (!empty($metaParts)) {
                    $addressLine = $addressLine !== '' ? ($addressLine . ', ' . $metaParts[0]) : $metaParts[0];
                }

                $image = $b->getLogoUrl();
                $image = is_string($image) && $image !== ''
                    ? $image
                    : 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80';

                $featured[] = [
                    'name' => $name !== '' ? $name : 'Bez nazwy',
                    'address' => $addressLine !== '' ? $addressLine : ($city !== '' ? $city : '—'),
                    'url' => $this->generateUrl('business_index', ['id' => $b->getId()]),
                    'rating' => $avgRating,
                    'reviews' => $reviewCount,
                    'image' => $image,
                    'promoted' => false,
                ];
            }
        }

        $slugger = new AsciiSlugger();

        $dbCities = $em->createQueryBuilder()
            ->select('DISTINCT b.city')
            ->from(Business::class, 'b')
            ->where('b.city IS NOT NULL')
            ->andWhere('b.city <> :empty')
            ->setParameter('empty', '')
            ->orderBy('b.city', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        $cityNames = !empty($dbCities) ? $dbCities : [];

        $cityNames = array_slice($cityNames, 0, 16);

        $cities = array_map(
            function (string $city) use ($slugger): array {
                return [
                    'name' => $city,
                    'slug' => (string) $slugger->slug($city)->lower(),
                ];
            },
            $cityNames
        );

        $blog = [
            [
                'tag' => 'Uroda',
                'title' => 'Jak dobrać idealną pielęgnację do typu cery?',
                'image' => 'https://images.unsplash.com/photo-1527799820374-dcf8d9d4a388?auto=format&fit=crop&w=1200&q=80',
                'href' => '#',
            ],
            [
                'tag' => 'Włosy',
                'title' => 'Najpopularniejsze trendy fryzjerskie w tym sezonie',
                'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=1200&q=80',
                'href' => '#',
            ],
            [
                'tag' => 'Relaks',
                'title' => 'Masaż: jak wybrać najlepszy dla siebie?',
                'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=1200&q=80',
                'href' => '#',
            ],
        ];

        $firstBusiness = null;
        $user = $this->getUser();
        if ($user && in_array('ROLE_BUSINESS_OWNER', $user->getRoles())) {
            $businesses = $this->businessRepository->findBy(['owner' => $user], ['id' => 'ASC'], 1);
            if (!empty($businesses)) {
                $firstBusiness = $businesses[0];
            }
        }

        return $this->render('home/index.html.twig', [
            'featured' => $featured,
            'cities' => $cities,
            'blog' => $blog,
            'firstBusiness' => $firstBusiness,
        ]);
    }
}
