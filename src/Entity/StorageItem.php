<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractUid;
use App\Entity\Interface\StorageItemInterface;
use App\Entity\Trait\TimestampTrait;
use App\Repository\StorageItemRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StorageItemRepository::class)]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(["file" => File::class, "folder" => Folder::class])]
#[ORM\HasLifecycleCallbacks]
abstract class StorageItem extends AbstractUid implements StorageItemInterface
{
    use TimestampTrait;

    #[ORM\Column(length: 127)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 127)]
    #[Assert\NoSuspiciousCharacters]
    #[Groups(["default"])]
    protected ?string $name = null;

    #[ORM\Column]
    #[Groups(["default"])]
    protected bool $inTrash = false;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(["default"])]
    protected int $version = 0;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[Ignore]
    protected ?Folder $folder = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    protected ?Workspace $workspace = null;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: AccessControl::class, orphanRemoval: true)]
    #[Groups(["item_details"])]
    protected Collection $accessControls;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTimeInterface $trashedAt = null;

    public function __construct()
    {
        parent::__construct();
        $this->accessControls = new ArrayCollection();
    }

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

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function setFolder(?Folder $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace): static
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * @return Collection<int, AccessControl>
     */
    public function getAccessControls(): Collection
    {
        return $this->accessControls;
    }

    public function addAccessControl(AccessControl $accessControl): static
    {
        if (!$this->accessControls->contains($accessControl)) {
            $this->accessControls->add($accessControl);
            $accessControl->setItem($this);
        }

        return $this;
    }

    public function removeAccessControl(AccessControl $accessControl): static
    {
        $this->accessControls->removeElement($accessControl);

        return $this;
    }
}