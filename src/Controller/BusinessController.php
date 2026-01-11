<?php

namespace App\Controller;

use App\Constants\BusinessFeaturesConstants;
use App\Entity\Business;
use App\Repository\BusinessRepository;
use App\Repository\ServiceRepository;
use App\Repository\StaffRepository;
use App\Repository\ReviewRepository;
use App\Repository\StaffServiceRepository;
use App\Service\GeocodingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BusinessController extends AbstractController
{
    public function __construct(
        private BusinessRepository $businessRepository,
        private ServiceRepository $serviceRepository,
        private StaffRepository $staffRepository,
        private ReviewRepository $reviewRepository,
        private StaffServiceRepository $staffServiceRepository,
        private GeocodingService $geocodingService,
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
            'category' => 'Barber Shop',
            'address_line' => $businessEntity->getAddress(),
            'city' => $businessEntity->getCity(),
            'postcode' => $businessEntity->getPostalCode(),
            'country' => 'Polska',
            'rating' => 5.0,
            'reviews_count' => count($businessEntity->getReviews()),
            'featured_image' => $businessEntity->getLogoUrl() ?? 'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1800&q=80',
            'email' => $businessEntity->getEmail(),
            'phone' => $businessEntity->getPhone(),
        ];

        // Get real services from database
        $serviceEntities = $this->serviceRepository->findBy(['business' => $businessEntity, 'isActive' => true]);
        $services = [];
        foreach ($serviceEntities as $serviceEntity) {
            $services[] = [
                'id' => $serviceEntity->getId(),
                'name' => $serviceEntity->getName(),
                'desc' => $serviceEntity->getDescription() ?? '',
                'price' => $serviceEntity->getPrice(),
                'duration' => $serviceEntity->getDurationMinutes() . ' min',
                'duration_minutes' => $serviceEntity->getDurationMinutes(),
                'images' => $serviceEntity->getFeaturedImage() ? [$serviceEntity->getFeaturedImage()] : [],
            ];
        }

        // Get real staff from database
        $staffEntities = $this->staffRepository->findBy(['business' => $businessEntity]);
        $staff = [];
        foreach ($staffEntities as $staffEntity) {
            $staff[] = [
                'id' => $staffEntity->getId(),
                'name' => $staffEntity->getName() . ' ' . $staffEntity->getSurname(),
                'avatar' => $staffEntity->getAvatarImage() ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=256&q=80',
            ];
        }

        // Get real reviews from database
        $reviewEntities = $this->reviewRepository->findBy(['business' => $businessEntity], ['createdAt' => 'DESC'], 10);
        $reviews = [];
        $totalRating = 0;
        foreach ($reviewEntities as $reviewEntity) {
            $reviews[] = [
                'rating' => $reviewEntity->getRating(),
                'service' => '',
                'staff' => '',
                'author' => $reviewEntity->getUser()->getName() . ' ' . substr($reviewEntity->getUser()->getSurname(), 0, 1) . '.',
                'date' => $reviewEntity->getCreatedAt()->format('Y-m-d'),
                'verified' => true,
                'comment' => $reviewEntity->getComment() ?? '',
            ];
            $totalRating += $reviewEntity->getRating();
        }

        $reviewsSummary = [
            'rating' => !empty($reviews) ? round($totalRating / count($reviews), 1) : 0,
            'count' => count($reviewEntities),
        ];

        // Update business rating with calculated value
        if (!empty($reviews)) {
            $business['rating'] = $reviewsSummary['rating'];
        }

        // Build staff-services mapping
        $staffServices = [];
        $staffServiceEntities = $this->staffServiceRepository->findAll();
        foreach ($staffServiceEntities as $ss) {
            $staffId = $ss->getStaff()->getId();
            $serviceId = $ss->getService()->getId();
            if (!isset($staffServices[$staffId])) {
                $staffServices[$staffId] = [];
            }
            $staffServices[$staffId][] = $serviceId;
        }


        $safetyRules = [];
        foreach ($businessEntity->getSafetyRules() as $ruleKey) {
            $safetyRules[] = [
                'label' => BusinessFeaturesConstants::getSafetyRuleLabel($ruleKey) ?? $ruleKey,
                'icon' => BusinessFeaturesConstants::getSafetyRuleIcon($ruleKey),
            ];
        }

        $amenities = [];
        foreach ($businessEntity->getAmenities() as $amenityKey) {
            $amenities[] = [
                'label' => BusinessFeaturesConstants::getAmenityLabel($amenityKey) ?? $amenityKey,
                'icon' => BusinessFeaturesConstants::getAmenityIcon($amenityKey),
            ];
        }

        $todayIndex = (int) date('N') - 1;

        // Geocode address for map
        $fullAddress = sprintf(
            '%s, %s %s, Poland',
            $businessEntity->getAddress(),
            $businessEntity->getPostalCode(),
            $businessEntity->getCity()
        );
        
        $coordinates = $this->geocodingService->geocodeAddress($fullAddress);
        
        $map = [
            'lat' => $coordinates['lat'] ?? 52.406376, // Default to Poland center
            'lng' => $coordinates['lng'] ?? 16.925167,
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
