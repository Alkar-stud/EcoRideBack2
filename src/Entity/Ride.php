<?php

namespace App\Entity;

use App\Repository\RideRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RideRepository::class)]
class Ride
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['trip_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['trip_read'])]
    private ?string $startingAddress = null;

    #[ORM\Column(length: 255)]
    #[Groups(['trip_read'])]
    private ?string $arrivalAddress = null;

    #[ORM\Column]
    #[Groups(['trip_read'])]
    private ?DateTimeImmutable $startingAt = null;

    #[ORM\Column]
    #[Groups(['trip_read'])]
    private ?int $duration = null;

    #[ORM\Column]
    #[Groups(['trip_read'])]
    private ?int $cost = null;

    #[ORM\Column]
    #[Groups(['trip_read'])]
    private ?int $maxNbPlaces = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['trip_read'])]
    private ?DateTimeImmutable $actualDepartureAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['trip_read'])]
    private ?DateTimeImmutable $actualArrivalAt = null;

    #[ORM\Column(length: 50)]
    #[Groups(['trip_read'])]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'ridesDriver')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['trip_read'])]
    private ?User $driver = null;

    #[ORM\ManyToOne(inversedBy: 'rides')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['trip_read'])]
    private ?Vehicle $vehicle = null;

    #[ORM\Column]
    #[Groups(['trip_read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['trip_read'])]
    private ?DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartingAddress(): ?string
    {
        return $this->startingAddress;
    }

    public function setStartingAddress(string $startingAddress): static
    {
        $this->startingAddress = $startingAddress;

        return $this;
    }

    public function getArrivalAddress(): ?string
    {
        return $this->arrivalAddress;
    }

    public function setArrivalAddress(string $arrivalAddress): static
    {
        $this->arrivalAddress = $arrivalAddress;

        return $this;
    }

    public function getStartingAt(): ?DateTimeImmutable
    {
        return $this->startingAt;
    }

    public function setStartingAt(DateTimeImmutable $startingAt): static
    {
        $this->startingAt = $startingAt;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(int $cost): static
    {
        $this->cost = $cost;

        return $this;
    }

    public function getMaxNbPlaces(): ?int
    {
        return $this->maxNbPlaces;
    }

    public function setMaxNbPlaces(int $maxNbPlaces): static
    {
        $this->maxNbPlaces = $maxNbPlaces;

        return $this;
    }

    public function getActualDepartureAt(): ?DateTimeImmutable
    {
        return $this->actualDepartureAt;
    }

    public function setActualDepartureAt(?DateTimeImmutable $actualDepartureAt): static
    {
        $this->actualDepartureAt = $actualDepartureAt;

        return $this;
    }

    public function getActualArrivalAt(): ?DateTimeImmutable
    {
        return $this->actualArrivalAt;
    }

    public function setActualArrivalAt(?DateTimeImmutable $actualArrivalAt): static
    {
        $this->actualArrivalAt = $actualArrivalAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

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
