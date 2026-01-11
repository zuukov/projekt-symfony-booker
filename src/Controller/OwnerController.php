<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Business;
use App\Entity\BusinessWorkingHours;
use App\Entity\Service;
use App\Entity\Staff;
use App\Entity\StaffService;
use App\Entity\StaffTimeOff;
use App\Entity\StaffWorkingHours;
use App\Entity\UserRole;
use App\Form\BusinessFormType;
use App\Form\ServiceFormType;
use App\Form\StaffFormType;
use App\Form\StaffTimeOffFormType;
use App\Form\StaffWorkingHoursFormType;
use App\Repository\BookingRepository;
use App\Repository\BusinessRepository;
use App\Repository\ServiceRepository;
use App\Repository\StaffRepository;
use App\Repository\StaffServiceRepository;
use App\Repository\StaffTimeOffRepository;
use App\Repository\StaffWorkingHoursRepository;
use App\Service\BookingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wlasciciel')]
#[IsGranted('ROLE_BUSINESS_OWNER')]
class OwnerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BusinessRepository $businessRepository,
        private StaffRepository $staffRepository,
        private ServiceRepository $serviceRepository,
        private StaffServiceRepository $staffServiceRepository,
        private StaffWorkingHoursRepository $staffWorkingHoursRepository,
        private StaffTimeOffRepository $staffTimeOffRepository,
        private BookingRepository $bookingRepository,
        private BookingService $bookingService,
    ) {}

    #[Route('/', name: 'owner_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER) {
            throw $this->createAccessDeniedException('Access denied. Business owner role required.');
        }

        $businesses = $this->businessRepository->findBy(['owner' => $user]);
        $firstBusiness = !empty($businesses) ? $businesses[0] : null;

        return $this->render('owner/dashboard.html.twig', [
            'businesses' => $businesses,
            'firstBusiness' => $firstBusiness,
        ]);
    }

    #[Route('/biznes/utworz', name: 'owner_business_create')]
    public function createBusiness(Request $request): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER) {
            throw $this->createAccessDeniedException('Access denied. Business owner role required.');
        }

        $existingBusiness = $this->businessRepository->findOneBy(['owner' => $user]);
        if ($existingBusiness) {
            $this->addFlash('error', 'Masz już utworzony biznes. Nie możesz utworzyć więcej niż jeden biznes.');
            return $this->redirectToRoute('owner_dashboard');
        }

        $business = new Business();
        $business->setOwner($user);

        for ($day = 0; $day <= 6; $day++) {
            $newHour = new BusinessWorkingHours();
            $newHour->setWeekday($day);
            $newHour->setBusiness($business);
            $business->addBusinessWorkingHour($newHour);
        }

        $form = $this->createForm(BusinessFormType::class, $business, [
            'is_edit' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workingHours = $business->getBusinessWorkingHours()->toArray();
            foreach ($workingHours as $hour) {
                if (!$hour->getOpensAt() || !$hour->getClosesAt()) {
                    $business->removeBusinessWorkingHour($hour);
                }
            }

            $this->entityManager->persist($business);
            $this->entityManager->flush();

            $this->addFlash('success', 'Business created successfully.');

            return $this->redirectToRoute('owner_business_staff', ['id' => $business->getId()]);
        }

        return $this->render('owner/business_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'is_edit' => false,
        ]);
    }

    #[Route('/biznes/{id}/edytuj', name: 'owner_business_edit')]
    public function editBusiness(Request $request, Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $existingHours = $business->getBusinessWorkingHours();
        $existingWeekdays = [];
        foreach ($existingHours as $hour) {
            $existingWeekdays[$hour->getWeekday()] = true;
        }

        for ($day = 0; $day <= 6; $day++) {
            if (!isset($existingWeekdays[$day])) {
                $newHour = new BusinessWorkingHours();
                $newHour->setWeekday($day);
                $newHour->setBusiness($business);
                $business->addBusinessWorkingHour($newHour);
            }
        }

        $form = $this->createForm(BusinessFormType::class, $business, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workingHours = $business->getBusinessWorkingHours()->toArray();
            foreach ($workingHours as $hour) {
                if (!$hour->getOpensAt() || !$hour->getClosesAt()) {
                    $business->removeBusinessWorkingHour($hour);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Business updated successfully.');

            return $this->redirectToRoute('owner_business_edit', ['id' => $business->getId()]);
        }

        return $this->render('owner/business_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'is_edit' => true,
        ]);
    }

    #[Route('/biznes/{id}/usun', name: 'owner_business_delete', methods: ['POST'])]
    public function deleteBusiness(Request $request, Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('delete'.$business->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($business);
            $this->entityManager->flush();

            $this->addFlash('success', 'Business deleted successfully.');
        }

        return $this->redirectToRoute('owner_dashboard');
    }

    #[Route('/biznes/{id}/personel', name: 'owner_business_staff')]
    public function listStaff(Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $staff = $this->staffRepository->findBy(['business' => $business]);

        return $this->render('owner/staff_list.html.twig', [
            'business' => $business,
            'staff' => $staff,
        ]);
    }

    #[Route('/biznes/{id}/personel/utworz', name: 'owner_staff_create')]
    public function createStaff(Request $request, Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $staff = new Staff();
        $staff->setBusiness($business);

        $form = $this->createForm(StaffFormType::class, $staff);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($staff);
            $this->entityManager->flush();

            $this->addFlash('success', 'Staff member added successfully.');

            return $this->redirectToRoute('owner_business_staff', ['id' => $business->getId()]);
        }

        return $this->render('owner/staff_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'staff' => $staff,
            'is_edit' => false,
        ]);
    }

    #[Route('/biznes/{businessId}/personel/{staffId}/edytuj', name: 'owner_staff_edit')]
    public function editStaff(Request $request, int $businessId, int $staffId): Response
    {
        $user = $this->getUser();

        $staff = $this->staffRepository->find($staffId);

        if (!$staff) {
            throw $this->createNotFoundException('Staff member not found.');
        }

        $business = $staff->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER ||
            $business->getOwner() !== $user ||
            $business->getId() !== $businessId) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $form = $this->createForm(StaffFormType::class, $staff);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Dane pracownika zostały zaktualizowane.');

            return $this->redirectToRoute('owner_business_staff', ['id' => $business->getId()]);
        }

        return $this->render('owner/staff_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'staff' => $staff,
            'is_edit' => true,
        ]);
    }

    #[Route('/biznes/{businessId}/personel/{staffId}/usun', name: 'owner_staff_delete', methods: ['POST'])]
    public function deleteStaff(Request $request, int $businessId, int $staffId): Response
    {
        $user = $this->getUser();

        $staff = $this->staffRepository->find($staffId);

        if (!$staff) {
            throw $this->createNotFoundException('Staff member not found.');
        }

        $business = $staff->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER ||
            $business->getOwner() !== $user ||
            $business->getId() !== $businessId) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('delete'.$staff->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($staff);
            $this->entityManager->flush();

            $this->addFlash('success', 'Staff member deleted successfully.');
        }

        return $this->redirectToRoute('owner_business_staff', ['id' => $business->getId()]);
    }

    #[Route('/biznes/{id}/pracownik/{staffId}/grafik', name: 'owner_staff_schedule')]
    public function staffSchedule(Request $request, Business $business, int $staffId): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $staff = $this->staffRepository->find($staffId);

        if (!$staff || $staff->getBusiness() !== $business) {
            throw $this->createNotFoundException('Staff member not found.');
        }

        $workingHours = $this->staffWorkingHoursRepository->findBy(['staff' => $staff], ['weekday' => 'ASC', 'startsAt' => 'ASC']);

        $workingHoursForm = $this->createForm(StaffWorkingHoursFormType::class);
        $workingHoursForm->handleRequest($request);

        if ($workingHoursForm->isSubmitted() && $workingHoursForm->isValid()) {
            $workingHour = $workingHoursForm->getData();
            $workingHour->setStaff($staff);

            $this->entityManager->persist($workingHour);
            $this->entityManager->flush();

            $this->addFlash('success', 'Godziny pracy zostały dodane.');

            return $this->redirectToRoute('owner_staff_schedule', [
                'id' => $business->getId(),
                'staffId' => $staff->getId()
            ]);
        }

        return $this->render('owner/staff_schedule.html.twig', [
            'business' => $business,
            'staff' => $staff,
            'workingHours' => $workingHours,
            'form' => $workingHoursForm->createView(),
        ]);
    }

    #[Route('/biznes/{businessId}/pracownik/{staffId}/grafik/{scheduleId}/usun', name: 'owner_staff_schedule_delete', methods: ['POST'])]
    public function deleteStaffSchedule(Request $request, int $businessId, int $staffId, int $scheduleId): Response
    {
        $user = $this->getUser();

        $schedule = $this->staffWorkingHoursRepository->find($scheduleId);

        if (!$schedule) {
            throw $this->createNotFoundException('Schedule not found.');
        }

        $staff = $schedule->getStaff();
        $business = $staff->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER ||
            $business->getOwner() !== $user ||
            $business->getId() !== $businessId ||
            $staff->getId() !== $staffId) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('delete'.$schedule->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($schedule);
            $this->entityManager->flush();

            $this->addFlash('success', 'Godziny pracy zostały usunięte.');
        }

        return $this->redirectToRoute('owner_staff_schedule', [
            'id' => $business->getId(),
            'staffId' => $staff->getId()
        ]);
    }

    #[Route('/biznes/{id}/pracownik/{staffId}/urlopy', name: 'owner_staff_timeoff')]
    public function staffTimeOff(Request $request, Business $business, int $staffId): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $staff = $this->staffRepository->find($staffId);

        if (!$staff || $staff->getBusiness() !== $business) {
            throw $this->createNotFoundException('Staff member not found.');
        }

        $timeOffs = $this->staffTimeOffRepository->findBy(['staff' => $staff], ['startsAt' => 'ASC']);

        $timeOffForm = $this->createForm(StaffTimeOffFormType::class);
        $timeOffForm->handleRequest($request);

        if ($timeOffForm->isSubmitted() && $timeOffForm->isValid()) {
            $timeOff = $timeOffForm->getData();
            $timeOff->setStaff($staff);

            $this->entityManager->persist($timeOff);
            $this->entityManager->flush();

            $this->addFlash('success', 'Nieobecność została dodana.');

            return $this->redirectToRoute('owner_staff_timeoff', [
                'id' => $business->getId(),
                'staffId' => $staff->getId()
            ]);
        }

        $now = new \DateTime();
        $upcomingTimeOffs = array_filter($timeOffs, fn($t) => $t->getStartsAt() >= $now);
        $pastTimeOffs = array_filter($timeOffs, fn($t) => $t->getStartsAt() < $now);

        return $this->render('owner/staff_timeoff.html.twig', [
            'business' => $business,
            'staff' => $staff,
            'upcomingTimeOffs' => $upcomingTimeOffs,
            'pastTimeOffs' => $pastTimeOffs,
            'form' => $timeOffForm->createView(),
        ]);
    }

    #[Route('/biznes/{businessId}/pracownik/{staffId}/urlopy/{timeoffId}/usun', name: 'owner_staff_timeoff_delete', methods: ['POST'])]
    public function deleteStaffTimeOff(Request $request, int $businessId, int $staffId, int $timeoffId): Response
    {
        $user = $this->getUser();

        $timeOff = $this->staffTimeOffRepository->find($timeoffId);

        if (!$timeOff) {
            throw $this->createNotFoundException('Time off not found.');
        }

        $staff = $timeOff->getStaff();
        $business = $staff->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER ||
            $business->getOwner() !== $user ||
            $business->getId() !== $businessId ||
            $staff->getId() !== $staffId) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('delete'.$timeOff->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($timeOff);
            $this->entityManager->flush();

            $this->addFlash('success', 'Nieobecność została usunięta.');
        }

        return $this->redirectToRoute('owner_staff_timeoff', [
            'id' => $business->getId(),
            'staffId' => $staff->getId()
        ]);
    }

    #[Route('/biznes/{id}/rezerwacje', name: 'owner_bookings')]
    public function businessBookings(Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $bookings = $this->bookingRepository->findByBusiness($business);

        return $this->render('owner/bookings.html.twig', [
            'business' => $business,
            'bookings' => $bookings,
        ]);
    }

    #[Route('/rezerwacja/{id}/potwierdz', name: 'owner_booking_confirm', methods: ['POST'])]
    public function confirmBooking(Request $request, Booking $booking): Response
    {
        $user = $this->getUser();
        $business = $booking->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('confirm'.$booking->getId(), $request->request->get('_token'))) {
            try {
                $this->bookingService->confirmBooking($booking);
                $this->addFlash('success', 'Rezerwacja została potwierdzona.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('owner_bookings', ['id' => $business->getId()]);
    }

    #[Route('/rezerwacja/{id}/zakoncz', name: 'owner_booking_complete', methods: ['POST'])]
    public function completeBooking(Request $request, Booking $booking): Response
    {
        $user = $this->getUser();
        $business = $booking->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('complete'.$booking->getId(), $request->request->get('_token'))) {
            try {
                $this->bookingService->completeBooking($booking);
                $this->addFlash('success', 'Rezerwacja została zakończona.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('owner_bookings', ['id' => $business->getId()]);
    }

    #[Route('/rezerwacja/{id}/anuluj', name: 'owner_booking_cancel', methods: ['POST'])]
    public function cancelOwnerBooking(Request $request, Booking $booking): Response
    {
        $user = $this->getUser();
        $business = $booking->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('cancel'.$booking->getId(), $request->request->get('_token'))) {
            try {
                $this->bookingService->cancelBooking($booking);
                $this->addFlash('success', 'Rezerwacja została anulowana.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('owner_bookings', ['id' => $business->getId()]);
    }

    #[Route('/biznes/{id}/pracownik/{staffId}/uslugi', name: 'owner_staff_services', methods: ['GET', 'POST'])]
    public function staffServices(Request $request, Business $business, int $staffId): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $staff = $this->staffRepository->find($staffId);

        if (!$staff || $staff->getBusiness() !== $business) {
            throw $this->createNotFoundException('Staff member not found.');
        }

        $allServices = $this->serviceRepository->findBy(['business' => $business]);
        $currentStaffServices = $this->staffServiceRepository->findServicesByStaff($staff);
        $currentServiceIds = array_map(fn($ss) => $ss->getService()->getId(), $currentStaffServices);

        if ($request->isMethod('POST')) {
            $selectedServiceIds = $request->request->all('services') ?? [];

            foreach ($currentStaffServices as $staffService) {
                if (!in_array($staffService->getService()->getId(), $selectedServiceIds)) {
                    $this->entityManager->remove($staffService);
                }
            }

            foreach ($selectedServiceIds as $serviceId) {
                if (!in_array($serviceId, $currentServiceIds)) {
                    $service = $this->serviceRepository->find($serviceId);
                    if ($service && $service->getBusiness() === $business) {
                        $newStaffService = new StaffService();
                        $newStaffService->setStaff($staff);
                        $newStaffService->setService($service);
                        $this->entityManager->persist($newStaffService);
                    }
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Usługi pracownika zostały zaktualizowane.');

            return $this->redirectToRoute('owner_staff_services', [
                'id' => $business->getId(),
                'staffId' => $staff->getId()
            ]);
        }

        return $this->render('owner/staff_services.html.twig', [
            'business' => $business,
            'staff' => $staff,
            'allServices' => $allServices,
            'currentServiceIds' => $currentServiceIds,
        ]);
    }

    #[Route('/biznes/{id}/uslugi', name: 'owner_services')]
    public function listServices(Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $services = $this->serviceRepository->findBy(['business' => $business], ['name' => 'ASC']);

        return $this->render('owner/service_list.html.twig', [
            'business' => $business,
            'services' => $services,
        ]);
    }

    #[Route('/biznes/{id}/uslugi/nowa', name: 'owner_service_create', methods: ['GET', 'POST'])]
    public function createService(Request $request, Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $service = new Service();
        $service->setBusiness($business);
        $service->setIsActive(true);

        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($service);
            $this->entityManager->flush();

            $this->addFlash('success', 'Usługa została utworzona.');

            return $this->redirectToRoute('owner_services', ['id' => $business->getId()]);
        }

        return $this->render('owner/service_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'service' => $service,
            'is_edit' => false,
        ]);
    }

    #[Route('/biznes/{businessId}/uslugi/{serviceId}/edytuj', name: 'owner_service_edit', methods: ['GET', 'POST'])]
    public function editService(Request $request, int $businessId, int $serviceId): Response
    {
        $user = $this->getUser();

        $service = $this->serviceRepository->find($serviceId);

        if (!$service) {
            throw $this->createNotFoundException('Service not found.');
        }

        $business = $service->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER ||
            $business->getOwner() !== $user ||
            $business->getId() !== $businessId) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Usługa została zaktualizowana.');

            return $this->redirectToRoute('owner_services', ['id' => $business->getId()]);
        }

        return $this->render('owner/service_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'service' => $service,
            'is_edit' => true,
        ]);
    }

    #[Route('/biznes/{businessId}/uslugi/{serviceId}/usun', name: 'owner_service_delete', methods: ['POST'])]
    public function deleteService(Request $request, int $businessId, int $serviceId): Response
    {
        $user = $this->getUser();

        $service = $this->serviceRepository->find($serviceId);

        if (!$service) {
            throw $this->createNotFoundException('Service not found.');
        }

        $business = $service->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER ||
            $business->getOwner() !== $user ||
            $business->getId() !== $businessId) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            $bookingCount = $this->bookingRepository->count(['service' => $service]);

            if ($bookingCount > 0) {
                $this->addFlash('error', 'Nie można usunąć usługi, która ma przypisane rezerwacje.');
                return $this->redirectToRoute('owner_services', ['id' => $business->getId()]);
            }

            $this->entityManager->remove($service);
            $this->entityManager->flush();

            $this->addFlash('success', 'Usługa została usunięta.');
        }

        return $this->redirectToRoute('owner_services', ['id' => $business->getId()]);
    }

    #[Route('/biznes/{id}/kalendarz', name: 'owner_calendar')]
    public function calendar(Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        return $this->render('owner/calendar.html.twig', [
            'business' => $business,
        ]);
    }

    #[Route('/biznes/{id}/kalendarz/dane', name: 'owner_calendar_data', methods: ['GET'])]
    public function calendarData(Business $business): JsonResponse
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        try {
            $bookings = $this->bookingRepository->findByBusiness($business);

            $events = [];
            foreach ($bookings as $booking) {
                if (!$booking->getStatus() || !$booking->getService() || !$booking->getStaff() || !$booking->getUser()) {
                    continue;
                }

                $color = match($booking->getStatus()->value) {
                    'pending' => '#fbbf24',
                    'confirmed' => '#10b981',
                    'completed' => '#3b82f6',
                    'cancelled' => '#ef4444',
                    default => '#6b7280'
                };

                $events[] = [
                    'id' => $booking->getId(),
                    'title' => $booking->getService()->getName() . ' - ' . $booking->getStaff()->getName(),
                    'start' => $booking->getStartsAt()->format('Y-m-d\TH:i:s'),
                    'end' => $booking->getEndsAt()->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'customerName' => $booking->getUser()->getName() . ' ' . $booking->getUser()->getSurname(),
                        'status' => $booking->getStatus()->value,
                        'price' => $booking->getPriceAtBooking(),
                    ]
                ];
            }

            return new JsonResponse($events);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to load calendar data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
