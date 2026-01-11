<?php

namespace App\Entity;

use App\Repository\BusinessWorkingHoursRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BusinessWorkingHoursRepository::class)]
class BusinessWorkingHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'businessWorkingHours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $business = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $weekday = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $opensAt = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $closesAt = null;

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

    public function getWeekday(): ?int
    {
        return $this->weekday;
    }

    public function setWeekday(int $weekday): static
    {
        $this->weekday = $weekday;

        return $this;
    }

    public function getOpensAt(): ?\DateTimeInterface
    {
        return $this->opensAt;
    }

    public function setOpensAt(?\DateTimeInterface $opensAt): static
    {
        $this->opensAt = $opensAt;

        return $this;
    }

    public function getClosesAt(): ?\DateTimeInterface
    {
        return $this->closesAt;
    }

    public function setClosesAt(?\DateTimeInterface $closesAt): static
    {
        $this->closesAt = $closesAt;

        return $this;
    }
}
