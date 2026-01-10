<?php

namespace App\Entity;

use App\Repository\BusinessRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: BusinessRepository::class)]
class Business
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'businesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $businessName = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $logoUrl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 500)]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 20)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    private ?string $formalBusinessName = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $secondaryPhone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $instagramUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $facebookUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $websiteUrl = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $specialNote = null;

    #[ORM\OneToMany(mappedBy: 'business', targetEntity: Service::class)]
    private Collection $services;

    #[ORM\OneToMany(mappedBy: 'business', targetEntity: Staff::class)]
    private Collection $staff;

    #[ORM\OneToMany(mappedBy: 'business', targetEntity: BusinessWorkingHours::class)]
    private Collection $businessWorkingHours;

    #[ORM\OneToMany(mappedBy: 'business', targetEntity: Booking::class)]
    private Collection $bookings;

    #[ORM\OneToMany(mappedBy: 'business', targetEntity: Review::class)]
    private Collection $reviews;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->staff = new ArrayCollection();
        $this->businessWorkingHours = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getBusinessName(): ?string
    {
        return $this->businessName;
    }

    public function setBusinessName(string $businessName): static
    {
        $this->businessName = $businessName;

        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): static
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getFormalBusinessName(): ?string
    {
        return $this->formalBusinessName;
    }

    public function setFormalBusinessName(string $formalBusinessName): static
    {
        $this->formalBusinessName = $formalBusinessName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getSecondaryPhone(): ?string
    {
        return $this->secondaryPhone;
    }

    public function setSecondaryPhone(?string $secondaryPhone): static
    {
        $this->secondaryPhone = $secondaryPhone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    public function setInstagramUrl(?string $instagramUrl): static
    {
        $this->instagramUrl = $instagramUrl;

        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): static
    {
        $this->facebookUrl = $facebookUrl;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): static
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setBusiness($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getBusiness() === $this) {
                $service->setBusiness(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Staff>
     */
    public function getStaff(): Collection
    {
        return $this->staff;
    }

    public function addStaff(Staff $staff): static
    {
        if (!$this->staff->contains($staff)) {
            $this->staff->add($staff);
            $staff->setBusiness($this);
        }

        return $this;
    }

    public function removeStaff(Staff $staff): static
    {
        if ($this->staff->removeElement($staff)) {
            // set the owning side to null (unless already changed)
            if ($staff->getBusiness() === $this) {
                $staff->setBusiness(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BusinessWorkingHours>
     */
    public function getBusinessWorkingHours(): Collection
    {
        return $this->businessWorkingHours;
    }

    public function addBusinessWorkingHour(BusinessWorkingHours $businessWorkingHour): static
    {
        if (!$this->businessWorkingHours->contains($businessWorkingHour)) {
            $this->businessWorkingHours->add($businessWorkingHour);
            $businessWorkingHour->setBusiness($this);
        }

        return $this;
    }

    public function removeBusinessWorkingHour(BusinessWorkingHours $businessWorkingHour): static
    {
        if ($this->businessWorkingHours->removeElement($businessWorkingHour)) {
            // set the owning side to null (unless already changed)
            if ($businessWorkingHour->getBusiness() === $this) {
                $businessWorkingHour->setBusiness(null);
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
            $booking->setBusiness($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getBusiness() === $this) {
                $booking->setBusiness(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setBusiness($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getBusiness() === $this) {
                $review->setBusiness(null);
            }
        }

        return $this;
    }

    public function getSpecialNote(): ?string
    {
        return $this->specialNote;
    }

    public function setSpecialNote(?string $specialNote): static
    {
        $this->specialNote = $specialNote;

        return $this;
    }
}
