<?php

namespace App\Controller;

use App\Entity\Business;
use App\Repository\BusinessRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BusinessController extends AbstractController
{
    public function __construct(
        private BusinessRepository $businessRepository,
    ) {}

    #[Route('/firma/{id}', name: 'business_index', requirements: ['id' => '\d+'])]
    public function index(int $id): Response
    {
        $businessEntity = $this->businessRepository->find($id);

        if (!$businessEntity) {
            throw $this->createNotFoundException('Business not found');
        }

        // Get photo gallery from real data (use photoUrls if available, fallback to logoUrl)
        $gallery = $businessEntity->getPhotoUrls();
        if (empty($gallery) && $businessEntity->getLogoUrl()) {
            $gallery = [$businessEntity->getLogoUrl()];
        }
        // Fallback to mock if no photos at all
        if (empty($gallery)) {
            $gallery = [
                'https://images.unsplash.com/photo-1521119989659-a83eee488004?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1517832606299-7ae9b720a186?auto=format&fit=crop&w=800&q=80',
            ];
        }

        // Convert BusinessWorkingHours entities to template format
        $openingHours = $this->formatOpeningHours($businessEntity);

        // Map business entity to array format expected by template
        $business = [
            'id' => $businessEntity->getId(),
            'name' => $businessEntity->getBusinessName(),
            'category' => 'Barber Shop', // TODO: Add category field to Business entity
            'address_line' => $businessEntity->getAddress(),
            'city' => $businessEntity->getCity(),
            'postcode' => $businessEntity->getPostalCode(),
            'country' => 'Polska',
            'rating' => 5.0, // TODO: Calculate from reviews
            'reviews_count' => 178, // TODO: Count reviews
            'featured_image' => $businessEntity->getLogoUrl() ?? 'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1800&q=80',
        ];

        $services = [
            [
                'id' => 101,
                'name' => 'Strzyżenie męskie',
                'desc' => 'Klasyczne strzyżenie z dopasowaniem do kształtu twarzy i stylu.',
                'price' => 60.00,
                'duration' => '45 min',
                'duration_minutes' => 45,
                'images' => [
                    'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=400&q=80',
                    'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=400&q=80',
                ],
            ],
            [
                'id' => 102,
                'name' => 'Broda / konturowanie',
                'desc' => 'Precyzyjne kontury, trymowanie i pielęgnacja brody kosmetykami premium.',
                'price' => 45.00,
                'duration' => '30 min',
                'duration_minutes' => 30,
                'images' => [
                    'https://images.unsplash.com/photo-1600334129128-685c5582fd35?auto=format&fit=crop&w=400&q=80',
                ],
            ],
            [
                'id' => 103,
                'name' => 'Pakiet: włosy + broda',
                'desc' => 'Kompletna usługa — strzyżenie + broda w jednej wizycie.',
                'price' => 95.00,
                'duration' => '75 min',
                'duration_minutes' => 75,
                'images' => [
                    'https://images.unsplash.com/photo-1522337660859-02fbefca4702?auto=format&fit=crop&w=400&q=80',
                ],
            ],
        ];


        $safetyRules = [
            ['label' => 'Wentylacja pomieszczeń', 'icon' => 'fa-regular fa-star'],
            ['label' => 'Regularna dezynfekcja stanowiska', 'icon' => 'fa-regular fa-star'],
            ['label' => 'Sterylizacja narzędzi', 'icon' => 'fa-regular fa-star'],
            ['label' => 'Możliwość płatności bezgotówkowej', 'icon' => 'fa-regular fa-star'],
        ];

        $amenities = [
            ['label' => 'Parking', 'icon' => 'fa-solid fa-square-parking'],
            ['label' => 'Internet (Wi-Fi)', 'icon' => 'fa-solid fa-wifi'],
            ['label' => 'Akceptacja kart płatniczych', 'icon' => 'fa-solid fa-credit-card'],
            ['label' => 'Dostępne dla niepełnosprawnych', 'icon' => 'fa-solid fa-wheelchair'],
            ['label' => 'Zwierzęta dozwolone', 'icon' => 'fa-solid fa-paw'],
            ['label' => 'Przyjazne dla dzieci', 'icon' => 'fa-solid fa-child-reaching'],
        ];

        $reviewsSummary = [
            'rating' => 5.0,
            'count' => 178,
        ];

        $reviews = [
            [
                'rating' => 5,
                'service' => 'Strzyżenie męskie',
                'staff' => 'Kuba',
                'author' => 'Sonia',
                'date' => '2026-01-05',
                'verified' => true,
                'comment' => 'Super klimat i bardzo dokładne cięcie. Na pewno wrócę!',
            ],
            [
                'rating' => 5,
                'service' => 'Pakiet: włosy + broda',
                'staff' => 'Mateusz',
                'author' => 'Michał',
                'date' => '2026-01-02',
                'verified' => true,
                'comment' => 'Perfekcyjne kontury brody, pełen profesjonalizm.',
            ],
            [
                'rating' => 5,
                'service' => 'Golenie brzytwą',
                'staff' => 'Kuba',
                'author' => 'Bartek',
                'date' => '2025-12-28',
                'verified' => true,
                'comment' => 'Rytuał golenia top. Skóra jak nowa.',
            ],
        ];

        $staff = [
            [
                'id' => 201,
                'name' => 'Kuba Nowak',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=256&q=80',
                'is_any' => true
            ],
            [
                'id' => 202,
                'name' => 'Mateusz Krawiec',
                'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=256&q=80',
            ],
            [
                'id' => 203,
                'name' => 'Ola Wiśniewska',
                'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=256&q=80',
            ],
            [
                'id' => 204,
                'name' => 'Ala Wiśniewska',
                'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=256&q=80',
            ],
            [
                'id' => 205,
                'name' => 'Kasia Wiśniewska',
                'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=256&q=80',
            ],
        ];

        $staffServices = [
            201 => [101, 102, 103],
            202 => [101, 103],
            203 => [102],
        ];

        $todayIndex = (int) date('N') - 1;

        $map = [
            'lat' => 51.585,
            'lng' => 14.969,
            'zoom' => 15,
        ];

        $about = $businessEntity->getDescription() ?? 'Jesteśmy miejscem, w którym liczy się detal. Stawiamy na klasykę, precyzję i dobrą atmosferę.';

        $socials = [
            'facebook' => $businessEntity->getFacebookUrl(),
            'instagram' => $businessEntity->getInstagramUrl(),
        ];

        $bookingAvailability = [];
        $today = new \DateTimeImmutable('today');

        $genSlots = static function(string $from, string $to): array {
            $start = \DateTimeImmutable::createFromFormat('H:i', $from);
            $end = \DateTimeImmutable::createFromFormat('H:i', $to);
            if (!$start || !$end) return [];

            $out = [];
            for ($t = $start; $t <= $end; $t = $t->modify('+30 minutes')) {
                $out[] = $t->format('H:i');
            }
            return $out;
        };

        for ($i = 1; $i <= 18; $i++) {
            $d = $today->modify("+{$i} day");
            $key = $d->format('Y-m-d');

            if (in_array($i, [4, 9, 15], true)) {
                continue;
            }

            $bookingAvailability[$key] = [
                'staff' => [
                    201 => $genSlots('10:00', '18:00'),
                    202 => ($i % 3 === 0) ? $genSlots('14:00', '18:30') : $genSlots('12:00', '19:00'),
                    203 => ($i % 2 === 0) ? $genSlots('09:00', '14:00') : [],
                ],
            ];

            foreach ($bookingAvailability[$key]['staff'] as $sid => $times) {
                if (empty($times)) {
                    unset($bookingAvailability[$key]['staff'][$sid]);
                }
            }

            if (empty($bookingAvailability[$key]['staff'])) {
                unset($bookingAvailability[$key]);
            }
        }

        return $this->render('business/index.html.twig', [
            'business' => $business,
            'staffServices' => $staffServices,
            'bookingAvailability' => $bookingAvailability,
            'gallery' => $gallery,
            'services' => $services,
            'safetyRules' => $safetyRules,
            'amenities' => $amenities,
            'reviewsSummary' => $reviewsSummary,
            'reviews' => $reviews,
            'staff' => $staff,
            'openingHours' => $openingHours,
            'todayIndex' => $todayIndex,
            'map' => $map,
            'about' => $about,
            'socials' => $socials,
        ]);
    }

    private function formatOpeningHours(Business $business): array
    {
        $dayNames = [
            'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek',
            'Piątek', 'Sobota', 'Niedziela'
        ];

        $hours = [];
        $workingHours = $business->getBusinessWorkingHours();

        // Create map of weekday => hours
        $hoursMap = [];
        foreach ($workingHours as $wh) {
            $hoursMap[$wh->getWeekday()] = $wh;
        }

        // Generate array for all 7 days
        for ($day = 0; $day <= 6; $day++) {
            $hours[] = [
                'day' => $dayNames[$day],
                'hours' => isset($hoursMap[$day]) && $hoursMap[$day]->getOpensAt() && $hoursMap[$day]->getClosesAt()
                    ? $hoursMap[$day]->getOpensAt()->format('H:i') . ' – ' . $hoursMap[$day]->getClosesAt()->format('H:i')
                    : 'Nieczynne'
            ];
        }

        return $hours;
    }
}
