<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'L’email est obligatoire.')]
    #[Assert\Email(message: 'L’email n’est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $firstName = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $lastName = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le type de compte est obligatoire.')]
    private ?string $accountType = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $emailVerificationCode = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Artist::class)]
    private Collection $artists;

    public function __construct()
    {
        $this->artists = new ArrayCollection();
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): ?string
    {
        return trim(implode(' ', array_filter([$this->firstName, $this->lastName])));
    }

    public function setFullName(string $fullName): static
    {
        $parts = preg_split('/\s+/', trim($fullName), 2) ?: [''];
        $this->firstName = $parts[0] ?? '';
        $this->lastName = $parts[1] ?? '';

        return $this;
    }

    public function getAccountType(): ?string
    {
        return $this->accountType;
    }

    public function setAccountType(string $accountType): static
    {
        $this->accountType = strtolower($accountType);

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->emailVerificationCode;
    }

    public function setEmailVerificationCode(?string $emailVerificationCode): static
    {
        $this->emailVerificationCode = $emailVerificationCode;

        return $this;
    }

    public function getAccountLabel(): string
    {
        return match ($this->accountType) {
            'artist' => 'Artiste',
            'auditeur' => 'Auditeur',
            'admin' => 'Administrateur',
            default => 'Utilisateur',
        };
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
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        switch ($this->accountType) {
            case 'artist':
                $roles[] = 'ROLE_ARTISTE';
                break;
            case 'auditeur':
                $roles[] = 'ROLE_AUDITEUR';
                break;
            case 'admin':
                $roles[] = 'ROLE_ADMIN';
                break;
        }

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
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
     * @return Collection<int, Artist>
     */
    public function getArtists(): Collection
    {
        return $this->artists;
    }

    public function addArtist(Artist $artist): static
    {
        if (!$this->artists->contains($artist)) {
            $this->artists->add($artist);
            $artist->setUser($this);
        }

        return $this;
    }

    public function removeArtist(Artist $artist): static
    {
        if ($this->artists->removeElement($artist)) {
            if ($artist->getUser() === $this) {
                $artist->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }
}
