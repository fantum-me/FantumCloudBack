<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractUid;
use App\Repository\WorkspaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkspaceRepository::class)]
class Workspace extends AbstractUid
{
    #[ORM\OneToMany(mappedBy: 'workspace', targetEntity: File::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $files;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'workspace', targetEntity: Folder::class, orphanRemoval: true)]
    private Collection $folders;

    #[Ignore]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Folder $rootFolder = null;

    #[ORM\Column(length: 31)]
    #[Assert\Length(min: 3, max: 31)]
    #[Assert\NoSuspiciousCharacters]
    #[Groups(["default"])]
    private ?string $name = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'workspace', targetEntity: Invite::class, orphanRemoval: true)]
    private Collection $invites;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'workspace', targetEntity: Member::class, orphanRemoval: true)]
    private Collection $members;

    #[ORM\OneToMany(mappedBy: 'workspace', targetEntity: Role::class, orphanRemoval: true)]
    #[ORM\OrderBy(["position" => "DESC"])]
    #[Groups(["workspace_details"])]
    private Collection $roles;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['workspace_details'])]
    private ?int $quota = null;

    public function __construct()
    {
        parent::__construct();
        $this->files = new ArrayCollection();
        $this->folders = new ArrayCollection();
        $this->invites = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->roles = new ArrayCollection();
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
            $file->setWorkspace($this);
        }

        return $this;
    }

    public function removeFile(File $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getWorkspace() === $this) {
                $file->setWorkspace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Folder>
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(Folder $folder): static
    {
        if (!$this->folders->contains($folder)) {
            $this->folders->add($folder);
            $folder->setWorkspace($this);
        }

        return $this;
    }

    public function removeFolder(Folder $folder): static
    {
        if ($this->folders->removeElement($folder)) {
            // set the owning side to null (unless already changed)
            if ($folder->getWorkspace() === $this) {
                $folder->setWorkspace(null);
            }
        }

        return $this;
    }

    public function getRootFolder(): ?Folder
    {
        return $this->rootFolder;
    }

    public function setRootFolder(?Folder $rootFolder): static
    {
        $this->rootFolder = $rootFolder;

        return $this;
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

    /**
     * @return Collection<int, Invite>
     */
    public function getInvites(): Collection
    {
        return $this->invites;
    }

    public function addInvite(Invite $invite): static
    {
        if (!$this->invites->contains($invite)) {
            $this->invites->add($invite);
            $invite->setWorkspace($this);
        }

        return $this;
    }

    public function removeInvite(Invite $invite): static
    {
        if ($this->invites->removeElement($invite)) {
            // set the owning side to null (unless already changed)
            if ($invite->getWorkspace() === $this) {
                $invite->setWorkspace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setWorkspace($this);
        }

        return $this;
    }

    public function removeMember(Member $member): static
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getWorkspace() === $this) {
                $member->setWorkspace(null);
            }
        }

        return $this;
    }

    #[Groups(["default"])]
    public function getMemberCount(): int
    {
        return $this->members->count();
    }

    #[Ignore]
    public function getOwner(): ?Member
    {
        foreach ($this->getMembers() as $member) {
            if ($member->isOwner()) {
                return $member;
            }
        }
        return null;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    #[Ignore]
    public function getDefaultRole(): Role
    {
        return $this->roles->filter(fn(Role $role) => $role->isDefault())->first();
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->setWorkspace($this);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        if ($this->roles->removeElement($role)) {
            // set the owning side to null (unless already changed)
            if ($role->getWorkspace() === $this) {
                $role->setWorkspace(null);
            }
        }

        return $this;
    }

    public function getQuota(): ?int
    {
        return $this->quota;
    }

    public function setQuota(?int $quota): static
    {
        $this->quota = $quota;

        return $this;
    }
}
