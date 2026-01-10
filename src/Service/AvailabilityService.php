<?php

namespace App\Service;

use App\Entity\Staff;
use App\Entity\Service;
use App\Repository\BookingRepository;
use App\Repository\StaffWorkingHoursRepository;
use App\Repository\StaffTimeOffRepository;

class AvailabilityService
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private StaffWorkingHoursRepository $staffWorkingHoursRepository,
        private StaffTimeOffRepository $staffTimeOffRepository
    ) {
    }

    public function getAvailableSlots(Staff $staff, Service $service, \DateTime $date): array
    {
        $weekday = (int)$date->format('N') - 1;

        $workingHours = $this->staffWorkingHoursRepository->findByStaffAndWeekday($staff, $weekday);

        if (empty($workingHours)) {
            return [];
        }

        if ($this->hasTimeOffOnDate($staff, $date)) {
            return [];
        }

        $dateStart = (clone $date)->setTime(0, 0, 0);
        $dateEnd = (clone $date)->setTime(23, 59, 59);

        $existingBookings = $this->bookingRepository->findByStaffAndDateRange(
            $staff,
            $dateStart,
            $dateEnd
        );

        $availableSlots = [];

        foreach ($workingHours as $workingHour) {
            $slots = $this->generateSlotsForWorkingHours($workingHour, $date, $service->getDurationMinutes());

            foreach ($slots as $slot) {
                if (!$this->hasBookingConflict($slot, $service->getDurationMinutes(), $existingBookings)) {
                    $availableSlots[] = $slot;
                }
            }
        }

        sort($availableSlots);

        return $availableSlots;
    }

    public function isSlotAvailable(Staff $staff, \DateTime $startsAt, \DateTime $endsAt): bool
    {
        $weekday = (int)$startsAt->format('N') - 1;

        $workingHours = $this->staffWorkingHoursRepository->findByStaffAndWeekday($staff, $weekday);

        if (empty($workingHours)) {
            return false;
        }

        $isWithinWorkingHours = false;
        foreach ($workingHours as $workingHour) {
            if ($this->isTimeWithinWorkingHours($startsAt, $endsAt, $workingHour)) {
                $isWithinWorkingHours = true;
                break;
            }
        }

        if (!$isWithinWorkingHours) {
            return false;
        }

        if ($this->hasTimeOffDuring($staff, $startsAt, $endsAt)) {
            return false;
        }

        $overlappingBookings = $this->bookingRepository->findOverlappingBookings($staff, $startsAt, $endsAt);

        return empty($overlappingBookings);
    }

    private function hasTimeOffOnDate(Staff $staff, \DateTime $date): bool
    {
        $dateStart = (clone $date)->setTime(0, 0, 0);
        $dateEnd = (clone $date)->setTime(23, 59, 59);

        $timeOffs = $this->staffTimeOffRepository->findByStaffAndDate($staff, $dateStart, $dateEnd);

        return !empty($timeOffs);
    }

    private function hasTimeOffDuring(Staff $staff, \DateTime $startsAt, \DateTime $endsAt): bool
    {
        $timeOffs = $this->staffTimeOffRepository->findByStaffAndDate($staff, $startsAt, $endsAt);

        return !empty($timeOffs);
    }

    private function generateSlotsForWorkingHours($workingHour, \DateTime $date, int $durationMinutes): array
    {
        $slots = [];

        $startTime = $workingHour->getStartsAt();
        $endTime = $workingHour->getEndsAt();

        $currentSlot = (clone $date)->setTime(
            (int)$startTime->format('H'),
            (int)$startTime->format('i'),
            0
        );

        $workingEndDateTime = (clone $date)->setTime(
            (int)$endTime->format('H'),
            (int)$endTime->format('i'),
            0
        );

        while ($currentSlot < $workingEndDateTime) {
            $slotEnd = (clone $currentSlot)->modify("+{$durationMinutes} minutes");

            if ($slotEnd <= $workingEndDateTime) {
                $slots[] = clone $currentSlot;
            }

            $currentSlot->modify("+{$durationMinutes} minutes");
        }

        return $slots;
    }

    private function hasBookingConflict(\DateTime $slot, int $durationMinutes, array $existingBookings): bool
    {
        $slotEnd = (clone $slot)->modify("+{$durationMinutes} minutes");

        foreach ($existingBookings as $booking) {
            if ($booking->getStatus()->value === 'CANCELLED') {
                continue;
            }

            $bookingStart = $booking->getStartsAt();
            $bookingEnd = $booking->getEndsAt();

            if ($slot < $bookingEnd && $slotEnd > $bookingStart) {
                return true;
            }
        }

        return false;
    }

    private function isTimeWithinWorkingHours(\DateTime $startsAt, \DateTime $endsAt, $workingHour): bool
    {
        $workingStart = $workingHour->getStartsAt();
        $workingEnd = $workingHour->getEndsAt();

        $startTime = $startsAt->format('H:i');
        $endTime = $endsAt->format('H:i');
        $workingStartTime = $workingStart->format('H:i');
        $workingEndTime = $workingEnd->format('H:i');

        return $startTime >= $workingStartTime && $endTime <= $workingEndTime;
    }
}
