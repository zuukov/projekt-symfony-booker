<?php

namespace App\Entity;

use App\Repository\StaffRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: StaffRepository::class)]
class Staff
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'staff')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $business = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $surname = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $avatarImage = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $aboutMe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $experience = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $school = null;

    #[ORM\OneToMany(mappedBy: 'staff', targetEntity: StaffService::class)]
    private Collection $staffServices;

    #[ORM\OneToMany(mappedBy: 'staff', targetEntity: StaffWorkingHours::class)]
    private Collection $staffWorkingHours;

    #[ORM\OneToMany(mappedBy: 'staff', targetEntity: StaffTimeOff::class)]
    private Collection $staffTimeOffs;

    #[ORM\OneToMany(mappedBy: 'staff', targetEntity: Booking::class)]
    private Collection $bookings;

    public function __construct()
    {
        $this->staffServices = new ArrayCollection();
        $this->staffWorkingHours = new ArrayCollection();
        $this->staffTimeOffs = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): static
    {
        $this->business = $business;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getAvatarImage(): ?string
    {
        return $this->avatarImage;
    }

    public function setAvatarImage(?string $avatarImage): static
    {
        $this->avatarImage = $avatarImage;

        return $this;
    }

    public function getAboutMe(): ?string
    {
        return $this->aboutMe;
    }

    public function setAboutMe(?string $aboutMe): static
    {
        $this->aboutMe = $aboutMe;

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): static
    {
        $this->experience = $experience;

        return $this;
    }

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(?string $school): static
    {
        $this->school = $school;

        return $this;
    }

    /**
     * @return Collection<int, StaffService>
     */
    public function getStaffServices(): Collection
    {
        return $this->staffServices;
    }

    public function addStaffService(StaffService $staffService): static
    {
        if (!$this->staffServices->contains($staffService)) {
            $this->staffServices->add($staffService);
            $staffService->setStaff($this);
        }

        return $this;
    }

    public function removeStaffService(StaffService $staffService): static
    {
        if ($this->staffServices->removeElement($staffService)) {
            if ($staffService->getStaff() === $this) {
                $staffService->setStaff(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StaffWorkingHours>
     */
    public function getStaffWorkingHours(): Collection
    {
        return $this->staffWorkingHours;
    }

    public function addStaffWorkingHour(StaffWorkingHours $staffWorkingHour): static
    {
        if (!$this->staffWorkingHours->contains($staffWorkingHour)) {
            $this->staffWorkingHours->add($staffWorkingHour);
            $staffWorkingHour->setStaff($this);
        }

        return $this;
    }

    public function removeStaffWorkingHour(StaffWorkingHours $staffWorkingHour): static
    {
        if ($this->staffWorkingHours->removeElement($staffWorkingHour)) {
            if ($staffWorkingHour->getStaff() === $this) {
                $staffWorkingHour->setStaff(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StaffTimeOff>
     */
    public function getStaffTimeOffs(): Collection
    {
        return $this->staffTimeOffs;
    }

    public function addStaffTimeOff(StaffTimeOff $staffTimeOff): static
    {
        if (!$this->staffTimeOffs->contains($staffTimeOff)) {
            $this->staffTimeOffs->add($staffTimeOff);
            $staffTimeOff->setStaff($this);
        }

        return $this;
    }

    public function removeStaffTimeOff(StaffTimeOff $staffTimeOff): static
    {
        if ($this->staffTimeOffs->removeElement($staffTimeOff)) {
            if ($staffTimeOff->getStaff() === $this) {
                $staffTimeOff->setStaff(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setStaff($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            if ($booking->getStaff() === $this) {
                $booking->setStaff(null);
            }
        }

        return $this;
    }
}
