<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use App\Repository\BusinessRepository;
use App\Repository\ServiceRepository;
use App\Repository\StaffRepository;
use App\Repository\StaffServiceRepository;
use App\Service\AvailabilityService;
use App\Service\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BookingController extends AbstractController
{
    public function __construct(
        private BusinessRepository $businessRepository,
        private ServiceRepository $serviceRepository,
        private StaffRepository $staffRepository,
        private StaffServiceRepository $staffServiceRepository,
        private BookingRepository $bookingRepository,
        private AvailabilityService $availabilityService,
        private BookingService $bookingService,
    ) {
    }

    #[Route('/rezerwacja/biznes/{businessId}/usluga/{serviceId}', name: 'booking_select_staff')]
    public function selectStaff(int $businessId, int $serviceId): Response
    {
        $business = $this->businessRepository->find($businessId);
        $service = $this->serviceRepository->find($serviceId);

        if (!$business || !$service || $service->getBusiness() !== $business) {
            throw $this->createNotFoundException('Firma lub usługa nie znaleziona.');
        }

        $staffServices = $this->staffServiceRepository->findBy(['service' => $service]);
        $staffMembers = array_map(fn($ss) => $ss->getStaff(), $staffServices);

        return $this->render('booking/select_staff.html.twig', [
            'business' => $business,
            'service' => $service,
            'staffMembers' => $staffMembers,
        ]);
    }

    #[Route('/rezerwacja/biznes/{businessId}/usluga/{serviceId}/pracownik/{staffId}', name: 'booking_select_datetime')]
    public function selectDateTime(int $businessId, int $serviceId, int $staffId): Response
    {
        $business = $this->businessRepository->find($businessId);
        $service = $this->serviceRepository->find($serviceId);
        $staff = $this->staffRepository->find($staffId);

        if (!$business || !$service || !$staff ||
            $service->getBusiness() !== $business ||
            $staff->getBusiness() !== $business) {
            throw $this->createNotFoundException('Nie znaleziono.');
        }

        $staffService = $this->staffServiceRepository->findOneBy([
            'staff' => $staff,
            'service' => $service
        ]);

        if (!$staffService) {
            throw $this->createNotFoundException('Ten pracownik nie świadczy tej usługi.');
        }

        return $this->render('booking/select_datetime.html.twig', [
            'business' => $business,
            'service' => $service,
            'staff' => $staff,
        ]);
    }

    #[Route('/rezerwacja/utworz', name: 'booking_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createBooking(Request $request): Response
    {
        $user = $this->getUser();

        $staffId = $request->request->get('staff_id');
        $serviceId = $request->request->get('service_id');
        $startsAt = $request->request->get('starts_at');

        $staff = $this->staffRepository->find($staffId);
        $service = $this->serviceRepository->find($serviceId);

        if (!$staff || !$service) {
            $this->addFlash('error', 'Nieprawidłowe dane rezerwacji.');
            return $this->redirectToRoute('app_home');
        }

        try {
            $startsAtDateTime = new \DateTime($startsAt);
            $booking = $this->bookingService->createBooking($user, $staff, $service, $startsAtDateTime);

            return $this->redirectToRoute('booking_confirmation', ['id' => $booking->getId()]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('booking_select_datetime', [
                'businessId' => $service->getBusiness()->getId(),
                'serviceId' => $service->getId(),
                'staffId' => $staff->getId()
            ]);
        }
    }

    #[Route('/rezerwacja/{id}/potwierdzenie', name: 'booking_confirmation')]
    #[IsGranted('ROLE_USER')]
    public function confirmation(Booking $booking): Response
    {
        $user = $this->getUser();

        if ($booking->getUser() !== $user) {
            throw $this->createAccessDeniedException('Nie masz dostępu do tej rezerwacji.');
        }

        return $this->render('booking/confirmation.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/uzytkownik/rezerwacje', name: 'user_bookings')]
    #[IsGranted('ROLE_USER')]
    public function userBookings(): Response
    {
        $user = $this->getUser();

        $upcomingBookings = $this->bookingRepository->findUpcomingByUser($user);
        $pastBookings = $this->bookingRepository->findPastByUser($user);

        return $this->render('user/bookings.html.twig', [
            'upcomingBookings' => $upcomingBookings,
            'pastBookings' => $pastBookings,
        ]);
    }

    #[Route('/rezerwacja/{id}/anuluj', name: 'booking_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancelBooking(Request $request, Booking $booking): Response
    {
        $user = $this->getUser();

        if ($booking->getUser() !== $user) {
            throw $this->createAccessDeniedException('Nie masz dostępu do tej rezerwacji.');
        }

        if ($this->isCsrfTokenValid('cancel'.$booking->getId(), $request->request->get('_token'))) {
            $now = new \DateTime();
            $startsAt = $booking->getStartsAt();
            $hoursUntilBooking = ($startsAt->getTimestamp() - $now->getTimestamp()) / 3600;

            if ($hoursUntilBooking < 2) {
                $this->addFlash('error', 'Nie można anulować rezerwacji na mniej niż 2 godziny przed terminem. Prosimy o kontakt telefoniczny z firmą: ' . $booking->getBusiness()->getPhone());
                return $this->redirectToRoute('user_bookings');
            }

            try {
                $this->bookingService->cancelBooking($booking);
                $this->addFlash('success', 'Rezerwacja została anulowana.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('user_bookings');
    }

    #[Route('/api/rezerwacja/sloty', name: 'api_booking_slots', methods: ['GET'])]
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $staffId = $request->query->get('staffId');
        $serviceId = $request->query->get('serviceId');
        $date = $request->query->get('date');

        if (!$staffId || !$serviceId || !$date) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Brak wymaganych parametrów'
            ], 400);
        }

        $staff = $this->staffRepository->find($staffId);
        $service = $this->serviceRepository->find($serviceId);

        if (!$staff || !$service) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Nie znaleziono pracownika lub usługi'
            ], 404);
        }

        try {
            $dateTime = new \DateTime($date);
            $slots = $this->availabilityService->getAvailableSlots($staff, $service, $dateTime);

            $slotsData = array_map(function($slot) {
                return [
                    'time' => $slot->format('H:i'),
                    'datetime' => $slot->format('Y-m-d\TH:i:s')
                ];
            }, $slots);

            return new JsonResponse([
                'success' => true,
                'date' => $date,
                'slots' => $slotsData
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Błąd podczas pobierania dostępnych terminów'
            ], 500);
        }
    }

    #[Route('/uzytkownik/kalendarz', name: 'user_calendar')]
    #[IsGranted('ROLE_USER')]
    public function userCalendar(): Response
    {
        return $this->render('user/calendar.html.twig');
    }

    #[Route('/uzytkownik/kalendarz/dane', name: 'user_calendar_data', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function userCalendarData(): JsonResponse
    {
        $user = $this->getUser();
        $bookings = $this->bookingRepository->findBy(['user' => $user]);

        $events = [];
        foreach ($bookings as $booking) {
            $color = match($booking->getStatus()->value) {
                'pending' => '#fbbf24',
                'confirmed' => '#10b981',
                'completed' => '#3b82f6',
                'cancelled' => '#ef4444',
                default => '#6b7280'
            };

            $events[] = [
                'id' => $booking->getId(),
                'title' => $booking->getService()->getName() . ' - ' . $booking->getBusiness()->getBusinessName(),
                'start' => $booking->getStartsAt()->format('Y-m-d\TH:i:s'),
                'end' => $booking->getEndsAt()->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'staffName' => $booking->getStaff()->getName() . ' ' . $booking->getStaff()->getSurname(),
                    'status' => $booking->getStatus()->value,
                    'price' => $booking->getPriceAtBooking(),
                    'businessPhone' => $booking->getBusiness()->getPhone(),
                ]
            ];
        }

        return new JsonResponse($events);
    }
}
