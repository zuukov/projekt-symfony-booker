<?php

namespace App\Entity;

use App\Repository\StaffWorkingHoursRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StaffWorkingHoursRepository::class)]
class StaffWorkingHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'staffWorkingHours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Staff $staff = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $weekday = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $startsAt = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $endsAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): static
    {
        $this->staff = $staff;

        return $this;
    }

    public function getWeekday(): ?int
    {
        return $this->weekday;
    }

    public function setWeekday(int $weekday): static
    {
        $this->weekday = $weekday;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeInterface $startsAt): static
    {
        // Convert DateTimeImmutable to DateTime for Doctrine TimeType compatibility
        if ($startsAt instanceof \DateTimeImmutable) {
            $this->startsAt = \DateTime::createFromInterface($startsAt);
        } else {
            $this->startsAt = $startsAt;
        }

        return $this;
    }

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeInterface $endsAt): static
    {
        // Convert DateTimeImmutable to DateTime for Doctrine TimeType compatibility
        if ($endsAt instanceof \DateTimeImmutable) {
            $this->endsAt = \DateTime::createFromInterface($endsAt);
        } else {
            $this->endsAt = $endsAt;
        }

        return $this;
    }
}
