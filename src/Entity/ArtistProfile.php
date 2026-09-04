<?php

namespace App\Entity;

use App\Repository\ArtistProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: ArtistProfileRepository::class)]
#[UniqueEntity(fields: ['stageName'], message: 'Ce nom de scène est déjà utilisé.')]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug est déjà utilisé.')]
#[Vich\Uploadable]
class ArtistProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, unique: true)]
    private ?string $stageName = null;

    #[ORM\Column(length: 150, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    /** Genre musical principal (ex: Rock, Jazz, Country…) */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[Vich\UploadableField(mapping: 'artist_photo', fileNameProperty: 'photo')]
    private ?File $photoFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    /** En attente de validation par un admin */
    #[ORM\Column]
    private bool $isApproved = false;

    #[ORM\OneToOne(inversedBy: 'artistProfile')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: Song::class)]
    private Collection $songs;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->songs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getStageName(): ?string { return $this->stageName; }
    public function setStageName(string $stageName): static { $this->stageName = $stageName; return $this; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getBio(): ?string { return $this->bio; }
    public function setBio(?string $bio): static { $this->bio = $bio; return $this; }

    public function getGenre(): ?string { return $this->genre; }
    public function setGenre(?string $genre): static { $this->genre = $genre; return $this; }

    public function getCountry(): ?string { return $this->country; }
    public function setCountry(?string $country): static { $this->country = $country; return $this; }

    public function getWebsite(): ?string { return $this->website; }
    public function setWebsite(?string $website): static { $this->website = $website; return $this; }

    public function getPhotoFile(): ?File { return $this->photoFile; }
    public function setPhotoFile(?File $photoFile): static
    {
        $this->photoFile = $photoFile;
        if ($photoFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): static { $this->photo = $photo; return $this; }

    public function isApproved(): bool { return $this->isApproved; }
    public function setIsApproved(bool $isApproved): static { $this->isApproved = $isApproved; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    /** @return Collection<int, Song> */
    public function getSongs(): Collection { return $this->songs; }

    public function addSong(Song $song): static
    {
        if (!$this->songs->contains($song)) {
            $this->songs->add($song);
            $song->setArtist($this);
        }
        return $this;
    }

    public function removeSong(Song $song): static
    {
        if ($this->songs->removeElement($song)) {
            if ($song->getArtist() === $this) {
                $song->setArtist(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function __toString(): string { return $this->stageName ?? ''; }
}
