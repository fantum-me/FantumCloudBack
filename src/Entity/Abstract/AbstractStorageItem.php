<?php

namespace App\Entity\Abstract;

use App\Entity\Interface\StorageItemInterface;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractStorageItem extends AbstractUid implements StorageItemInterface
{
    #[ORM\Column(length: 127)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 127)]
    #[Assert\NoSuspiciousCharacters]
    protected ?string $name = null;

    #[ORM\Column]
    protected bool $inTrash = false;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    protected int $version = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTimeInterface $trashedAt = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSystemFileName(): ?string
    {
        return $this->id;
    }

    public function getFullSystemFileName(): ?string
    {
        return $this->getSystemFileName();
    }

    public function getPath(): ?string
    {
        if ($folder = $this->getFolder()) {
            return $folder->getPath() . "/" . $this->getFullSystemFileName();
        } else {
            return $this->getWorkspace()->getId();
        }
    }

    public function isInTrash($checkParents = false): ?bool
    {
        if ($checkParents && $parent = $this->getFolder()) return $this->inTrash || $parent->isInTrash(true);
        else return $this->inTrash;
    }

    public function setInTrash(bool $inTrash): static
    {
        $this->inTrash = $inTrash;
        if ($this->inTrash) $this->trashedAt = new DateTime();
        else $this->trashedAt = null;

        return $this;
    }

    public function getTrashedAt(): ?DateTimeInterface
    {
        return $this->trashedAt;
    }

    public function updateVersion($withParent = true): void
    {
        $this->version++;
        if ($withParent) {
            $parent = $this->getFolder();
            while ($parent) {
                $parent->updateVersion(false);
                $parent = $parent->getFolder();
            }
        }
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
