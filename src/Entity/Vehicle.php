<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vehicle_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicle_read'])]
    private ?string $brand = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicle_read'])]
    private ?string $model = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicle_read'])]
    private ?string $color = null;

    #[ORM\Column(length: 20)]
    #[Groups(['vehicle_read'])]
    private ?string $licensePlate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['vehicle_read'])]
    private ?\DateTime $licenseFirstDate = null;

    #[ORM\Column]
    #[Groups(['vehicle_read'])]
    private ?int $nbPlace = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vehicle_read'])]
    private ?Energy $energy = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "vehicles")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getLicensePlate(): ?string
    {
        return $this->licensePlate;
    }

    public function setLicensePlate(string $licensePlate): static
    {
        $this->licensePlate = $licensePlate;

        return $this;
    }

    public function getLicenseFirstDate(): ?DateTime
    {
        return $this->licenseFirstDate;
    }

    public function setLicenseFirstDate(DateTime $licenseFirstDate): static
    {
        $this->licenseFirstDate = $licenseFirstDate;

        return $this;
    }

    public function getNbPlace(): ?int
    {
        return $this->nbPlace;
    }

    public function setNbPlace(int $nbPlace): static
    {
        $this->nbPlace = $nbPlace;

        return $this;
    }

    public function getEnergy(): ?Energy
    {
        return $this->energy;
    }

    public function setEnergy(?Energy $energy): static
    {
        $this->energy = $energy;

        return $this;
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
