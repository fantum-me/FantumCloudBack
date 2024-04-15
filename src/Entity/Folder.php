<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractStorageItem;
use App\Entity\Trait\TimestampTrait;
use App\Repository\FolderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FolderRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Folder extends AbstractStorageItem
{
    use TimestampTrait;

    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: File::class, orphanRemoval: true)]
    private Collection $files;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'folders')]
    private ?self $folder = null;

    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: self::class)]
    private Collection $folders;

    #[ORM\ManyToOne(inversedBy: 'folders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Workspace $workspace = null;

    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: AccessControl::class)]
    private Collection $accessControls;

    public function __construct()
    {
        parent::__construct();
        $this->files = new ArrayCollection();
        $this->folders = new ArrayCollection();
        $this->accessControls = new ArrayCollection();
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setFolder($this);
        }

        return $this;
    }

    public function removeFile(File $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getFolder() === $this) {
                $file->setFolder(null);
            }
        }

        return $this;
    }

    public function getFolder(): ?self
    {
        return $this->folder;
    }

    public function setFolder(?self $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(self $folder): static
    {
        if (!$this->folders->contains($folder)) {
            $this->folders->add($folder);
            $folder->setFolder($this);
        }

        return $this;
    }

    public function removeFolder(self $folder): static
    {
        if ($this->folders->removeElement($folder)) {
            // set the owning side to null (unless already changed)
            if ($folder->getFolder() === $this) {
                $folder->setFolder(null);
            }
        }

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

    public function addLocalPermission(AccessControl $localPermission): static
    {
        if (!$this->accessControls->contains($localPermission)) {
            $this->accessControls->add($localPermission);
            $localPermission->setItem($this);
        }

        return $this;
    }

    public function removeLocalPermission(AccessControl $localPermission): static
    {
        $this->accessControls->removeElement($localPermission);

        return $this;
    }
}