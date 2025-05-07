<?php

namespace App\Entity;

use App\Repository\EnergyRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EnergyRepository::class)]
class Energy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicle_read'])]
    private ?string $libelle = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['vehicle_read'])]
    private ?bool $isEco = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): static
    {
        if ($libelle !== null) {
            $this->libelle = mb_convert_case(trim($libelle), MB_CASE_TITLE, "UTF-8");
        } else {
            $this->libelle = null;
        }

        return $this;
    }

    public function isEco(): ?bool
    {
        return $this->isEco;
    }

    public function setIsEco(?bool $isEco): static
    {
        $this->isEco = $isEco;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
