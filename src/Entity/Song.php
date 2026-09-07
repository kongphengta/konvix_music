<?php

namespace App\Entity;

use App\Entity\ArtistProfile;
use App\Repository\SongRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: SongRepository::class)]
#[Vich\Uploadable]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    /** Nom du fichier cover (JPG carré 1200-3000 px) */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'song_cover', fileNameProperty: 'image')]
    private ?File $coverFile = null;

    /** Nom du fichier audio (.wav ou .mp3) */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[Vich\UploadableField(mapping: 'song_audio', fileNameProperty: 'filename')]
    private ?File $audioFile = null;

    #[ORM\ManyToOne(inversedBy: 'songs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'songs')]
    private ?Album $album = null;

    #[ORM\ManyToOne(inversedBy: 'songs')]
    private ?ArtistProfile $artist = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $listenCount = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->publishedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getCoverFile(): ?File { return $this->coverFile; }
    public function setCoverFile(?File $coverFile): static
    {
        $this->coverFile = $coverFile;
        if ($coverFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(?string $filename): static { $this->filename = $filename; return $this; }

    public function getAudioFile(): ?File { return $this->audioFile; }
    public function setAudioFile(?File $audioFile): static
    {
        $this->audioFile = $audioFile;
        if ($audioFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;
        return $this;
    }

    public function getArtist(): ?ArtistProfile { return $this->artist; }
    public function setArtist(?ArtistProfile $artist): static { $this->artist = $artist; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt ?? $this->createdAt;
    }

    public function getPublishedAgo(): string
    {
        $publishedAt = $this->getPublishedAt();

        if (!$publishedAt) {
            return '—';
        }

        $now = new \DateTimeImmutable();
        $seconds = max(0, $now->getTimestamp() - $publishedAt->getTimestamp());

        $hours = intdiv($seconds, 3600);
        if ($hours < 24) {
            return $this->formatRelativeValue($hours, 'h');
        }

        $days = intdiv($seconds, 86400);
        if ($days < 30) {
            return $this->formatRelativeValue($days, 'j');
        }

        $months = intdiv($days, 30);
        if ($months < 12) {
            return $this->formatRelativeValue($months, 'mois');
        }

        $years = intdiv($months, 12);
        return $this->formatRelativeValue($years, 'an');
    }

    private function formatRelativeValue(int $value, string $unit): string
    {
        $value = max(1, $value);

        if ($unit === 'h') {
            return 'il y a ' . $value . ' h';
        }

        if ($unit === 'j') {
            return 'il y a ' . $value . ' j';
        }

        if ($unit === 'mois') {
            return 'il y a ' . $value . ' mois';
        }

        return 'il y a ' . $value . ' ' . ($value > 1 ? 'ans' : 'an');
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getListenCount(): int
    {
        return $this->listenCount;
    }

    public function setListenCount(int $listenCount): static
    {
        $this->listenCount = max(0, $listenCount);
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function __toString(): string { return $this->title ?? ''; }
}
