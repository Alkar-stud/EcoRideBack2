<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Random\RandomException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Table(name: '"user"')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_read', 'notice_detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user_login', 'user_read', 'notice_read'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user_login', 'user_read'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     * @Assert\NotBlank(message="Le mot de passe ne peut pas être vide.")
     * @Assert\Regex(
     *     pattern="/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/",
     *     message="Le mot de passe doit contenir au moins 8 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial."
     * )
     */
    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 255)]
    #[Groups(['user_login', 'user_read', 'trip_read', 'notice_detail'])]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user_read'])]
    private ?string $photo = null;

    #[ORM\Column]
    #[Groups(['user_read'])]
    private ?int $credits = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user_read', 'notice_read'])]
    private ?int $grade = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user_read'])]
    private ?bool $isDriver = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user_read'])]
    private ?bool $isPassenger = null;

    #[ORM\Column(length: 64)]
    #[Groups(['user_login'])]
    private ?string $apiToken;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column]
    #[Groups(['user_read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user_read'])]
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Preferences>
     */
    #[ORM\OneToMany(targetEntity: Preferences::class, mappedBy: 'userPreferences', orphanRemoval: true)]
    #[Groups(['user_read'])]
    private Collection $preferences;

    /**
     * @var Collection<int, Vehicle>
     */
    #[ORM\OneToMany(targetEntity: Vehicle::class, mappedBy: 'owner', orphanRemoval: true)]
    #[Groups(['user_read'])]
    private Collection $vehicles;


    /**
     * @throws RandomException
     */
    public function __construct()
    {
        $this->apiToken = bin2hex(random_bytes(32));
        $this->preferences = new ArrayCollection();
        $this->vehicles = new ArrayCollection();
        //$this->trips = new ArrayCollection();
        //$this->tripsUsers = new ArrayCollection();
        //$this->noticesPublisher = new ArrayCollection();
        //$this->noticesToValidate = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Ajouter le préfixe ROLE_ et convertir en majuscules
        $roles = array_map(function ($role) {
            return 'ROLE_' . strtoupper($role);
        }, $roles);

        // Garantir que chaque utilisateur a au moins ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<array> $roles
     */
    public function setRoles(array $roles): static
    {
        // Supprimer ROLE_USER s'il est présent
        $roles = array_filter($roles, function ($role) {
            return $role !== 'ROLE_USER';
        });

        // Retirer le préfixe ROLE_ et convertir en minuscules
        $roles = array_map(function ($role) {
            return strtolower(preg_replace('/^ROLE_/', '', $role));
        }, $roles);

        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): static
    {
        $this->credits = $credits;

        return $this;
    }

    public function getGrade(): ?int
    {
        return $this->grade;
    }

    public function setGrade(?int $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function isDriver(): ?bool
    {
        return $this->isDriver;
    }

    public function setIsDriver(?bool $isDriver): static
    {
        $this->isDriver = $isDriver;

        return $this;
    }

    public function isPassenger(): ?bool
    {
        return $this->isPassenger;
    }

    public function setIsPassenger(?bool $isPassenger): static
    {
        $this->isPassenger = $isPassenger;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

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

    /**
     * @return Collection<int, Preferences>
     */
    public function getPreferences(): Collection
    {
        return $this->preferences;
    }

    public function addPreference(Preferences $preference): static
    {
        if (!$this->preferences->contains($preference)) {
            $this->preferences->add($preference);
            $preference->setUserPreferences($this);
        }

        return $this;
    }

    public function removePreference(Preferences $preference): static
    {
        if ($this->preferences->removeElement($preference)) {
            // set the owning side to null (unless already changed)
            if ($preference->getUserPreferences() === $this) {
                $preference->setUserPreferences(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setOwner($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            // set the owning side to null (unless already changed)
            if ($vehicle->getOwner() === $this) {
                $vehicle->setOwner(null);
            }
        }

        return $this;
    }

}
