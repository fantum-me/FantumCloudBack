<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractStorageItem;
use App\Entity\Trait\TimestampTrait;
use App\Repository\FileRepository;
use App\Service\StorageItem\FilePreviewService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class File extends AbstractStorageItem
{
    use TimestampTrait;

    #[ORM\Column(length: 255)]
    #[Groups(["file_details"])]
    private ?string $ext = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex("^[a-zA-Z]+\/[a-zA-Z0-9\-\.\+]+$^")]
    #[SerializedName("mime")]
    #[Groups(["file_details"])]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    #[Ignore]
    private ?Folder $folder = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Workspace $workspace = null;

    #[ORM\OneToMany(mappedBy: 'file', targetEntity: AccessControl::class, orphanRemoval: true)]
    #[Groups(["file_details"])]
    private Collection $accessControls;

    public function __construct()
    {
        parent::__construct();
        $this->accessControls = new ArrayCollection();
    }

    public function getFullSystemFileName(): ?string
    {
        return $this->getSystemFileName() . "." . $this->getExt();
    }

    public function getPreviewPath(): ?string
    {
        return $this->folder->getPath() . "/preview-" . $this->getSystemFileName(
            ) . "." . FilePreviewService::PREVIEW_FORMAT;
    }

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function setExt(string $ext): static
    {
        $this->ext = $ext;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
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
