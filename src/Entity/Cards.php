<?php

namespace App\Entity;

use App\Repository\CardsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardsRepository::class)]
class Cards
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $user_id = null;

    #[ORM\Column(length: 16)]
    private ?string $card_number = null;

    #[ORM\Column(length: 5)]
    private ?string $valid_until = null;

    #[ORM\Column]
    private ?int $cvc_code = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $is_default = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCardNumber(): ?string
    {
        return $this->card_number;
    }

    public function setCardNumber(string $card_number): static
    {
        $this->card_number = $card_number;

        return $this;
    }

    public function getValidUntil(): ?string
    {
        return $this->valid_until;
    }

    public function setValidUntil(string $valid_until): static
    {
        $this->valid_until = $valid_until;

        return $this;
    }

    public function getCvcCode(): ?int
    {
        return $this->cvc_code;
    }

    public function setCvcCode(int $cvc_code): static
    {
        $this->cvc_code = $cvc_code;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function isDefault(): ?bool
    {
        return $this->is_default;
    }

    public function setIsDefault(bool $is_default): static
    {
        $this->is_default = $is_default;

        return $this;
    }
}
