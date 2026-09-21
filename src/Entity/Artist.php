<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
#[ORM\Table(name: 'artist')]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    private string $slug;

    #[ORM\Column(type: 'string', length: 180)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: 'string', length: 180)]
    #[Assert\NotBlank]
    private string $genre;

    #[ORM\Column(type: 'string', length: 120)]
    #[Assert\NotBlank]
    private string $city;

    #[ORM\Column(type: 'string', length: 40)]
    #[Assert\NotBlank]
    private string $followers;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', length: 40)]
    #[Assert\NotBlank]
    private string $color;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'artists')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublished = false;

    /**
     * @var Collection<int, Track>
     */
    #[ORM\OneToMany(targetEntity: Track::class, mappedBy: 'artist')]
    private Collection $tracks;

    public function __construct()
    {
        $this->tracks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGenre(): string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getFollowers(): string
    {
        return $this->followers;
    }

    public function setFollowers(string $followers): self
    {
        $this->followers = $followers;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * @return Collection<int, Track>
     */
    public function getTracks(): Collection
    {
        return $this->tracks;
    }

    public function addTrack(Track $track): static
    {
        if (!$this->tracks->contains($track)) {
            $this->tracks->add($track);
            $track->setArtist($this);
        }

        return $this;
    }

    public function removeTrack(Track $track): static
    {
        if ($this->tracks->removeElement($track)) {
            // set the owning side to null (unless already changed)
            if ($track->getArtist() === $this) {
                $track->setArtist(null);
            }
        }

        return $this;
    }
}
