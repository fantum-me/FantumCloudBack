<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractUid;
use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member extends AbstractUid
{
    #[ORM\ManyToOne(inversedBy: 'relatedMembers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Workspace $workspace = null;

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: Invite::class, orphanRemoval: true)]
    private Collection $invites;

    #[ORM\Column]
    private bool $isOwner = false;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'members')]
    #[ORM\OrderBy(["position" => "DESC"])]
    private Collection $roles;

    public function __construct()
    {
        parent::__construct();
        $this->invites = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
            $invite->setCreatedBy($this);
        }

        return $this;
    }

    public function removeInvite(Invite $invite): static
    {
        if ($this->invites->removeElement($invite)) {
            // set the owning side to null (unless already changed)
            if ($invite->getCreatedBy() === $this) {
                $invite->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function isOwner(): bool
    {
        return $this->isOwner;
    }

    public function setIsOwner(bool $isOwner): static
    {
        $this->isOwner = $isOwner;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }
}
