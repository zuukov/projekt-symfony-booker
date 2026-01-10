<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\BookingStatus;
use App\Entity\Staff;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\StaffServiceRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookingService
{
    public function __construct(
        private AvailabilityService $availabilityService,
        private EntityManagerInterface $entityManager,
        private StaffServiceRepository $staffServiceRepository
    ) {
    }

    public function createBooking(User $user, Staff $staff, Service $service, \DateTime $startsAt): Booking
    {
        $errors = $this->validateBookingRequest($staff, $service, $startsAt);

        if (!empty($errors)) {
            throw new \RuntimeException(implode(', ', $errors));
        }

        $endsAt = (clone $startsAt)->modify("+{$service->getDurationMinutes()} minutes");

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setStaff($staff);
        $booking->setService($service);
        $booking->setBusiness($service->getBusiness());
        $booking->setStartsAt($startsAt);
        $booking->setEndsAt($endsAt);
        $booking->setPriceAtBooking((string)$service->getPrice());
        $booking->setStatus(BookingStatus::PENDING);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $booking;
    }

    public function cancelBooking(Booking $booking): void
    {
        if ($booking->getStatus() === BookingStatus::CANCELLED) {
            throw new \RuntimeException('Rezerwacja jest już anulowana');
        }

        if ($booking->getStatus() === BookingStatus::COMPLETED) {
            throw new \RuntimeException('Nie można anulować zakończonej rezerwacji');
        }

        $booking->setStatus(BookingStatus::CANCELLED);
        $this->entityManager->flush();
    }

    public function confirmBooking(Booking $booking): void
    {
        if ($booking->getStatus() !== BookingStatus::PENDING) {
            throw new \RuntimeException('Tylko oczekujące rezerwacje mogą być potwierdzone');
        }

        $booking->setStatus(BookingStatus::CONFIRMED);
        $this->entityManager->flush();
    }

    public function completeBooking(Booking $booking): void
    {
        if ($booking->getStatus() === BookingStatus::CANCELLED) {
            throw new \RuntimeException('Nie można zakończyć anulowanej rezerwacji');
        }

        $booking->setStatus(BookingStatus::COMPLETED);
        $this->entityManager->flush();
    }

    public function validateBookingRequest(Staff $staff, Service $service, \DateTime $startsAt): array
    {
        $errors = [];

        $staffService = $this->staffServiceRepository->findOneBy([
            'staff' => $staff,
            'service' => $service
        ]);

        if (!$staffService) {
            $errors[] = 'Wybrany pracownik nie świadczy tej usługi';
        }

        $now = new \DateTime();
        if ($startsAt <= $now) {
            $errors[] = 'Nie można zarezerwować terminu w przeszłości';
        }

        if (!$service->isActive()) {
            $errors[] = 'Usługa nie jest aktywna';
        }

        $endsAt = (clone $startsAt)->modify("+{$service->getDurationMinutes()} minutes");

        if (!$this->availabilityService->isSlotAvailable($staff, $startsAt, $endsAt)) {
            $errors[] = 'Wybrany termin nie jest dostępny';
        }

        return $errors;
    }
}
