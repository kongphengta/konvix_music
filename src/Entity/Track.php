<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
#[Vich\Uploadable]
class Track
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tracks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $artist = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $audioFileName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImageName = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Vich\UploadableField(mapping: 'tracks_audio', fileNameProperty: 'audioFileName')]
    private ?File $audioFile = null;

    #[Vich\UploadableField(mapping: 'tracks_cover', fileNameProperty: 'coverImageName')]
    private ?File $coverImage = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArtist(): ?User
    {
        return $this->artist;
    }

    public function setArtist(?User $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAudioFileName(): ?string
    {
        return $this->audioFileName;
    }

    public function setAudioFileName(?string $audioFileName): static
    {
        $this->audioFileName = $audioFileName;

        return $this;
    }

    public function getCoverImageName(): ?string
    {
        return $this->coverImageName;
    }

    public function setCoverImageName(?string $coverImageName): static
    {
        $this->coverImageName = $coverImageName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAudioFile(): ?File
    {
        return $this->audioFile;
    }

    public function setAudioFile(?File $audioFile): static
    {
        $this->audioFile = $audioFile;

        if ($audioFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getCoverImage(): ?File
    {
        return $this->coverImage;
    }

    public function setCoverImage(?File $coverImage): static
    {
        $this->coverImage = $coverImage;

        if ($coverImage) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }
}
