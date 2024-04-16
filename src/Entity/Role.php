<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractPermissionManager;
use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role extends AbstractPermissionManager
{
    #[ORM\Column(length: 31)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 31)]
    #[Assert\NoSuspiciousCharacters]
    #[Groups(["default"])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\CssColor(Assert\CssColor::HEX_LONG)]
    #[Groups(["default"])]
    private ?string $color = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'roles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Workspace $workspace = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'role', targetEntity: AccessControl::class, orphanRemoval: true)]
    private Collection $accessControls;

    #[Ignore]
    #[ORM\ManyToMany(targetEntity: Member::class, mappedBy: 'roles')]
    private Collection $members;

    #[ORM\Column]
    #[Groups(["default"])]
    private bool $isDefault = false;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(["default"])]
    private ?int $position = null;

    public function __construct()
    {
        parent::__construct();
        $this->accessControls = new ArrayCollection();
        $this->members = new ArrayCollection();
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

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
            $localPermission->setRole($this);
        }

        return $this;
    }

    public function removeLocalPermission(AccessControl $localPermission): static
    {
        if ($this->accessControls->removeElement($localPermission)) {
            // set the owning side to null (unless already changed)
            if ($localPermission->getRole() === $this) {
                $localPermission->setRole(null);
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
            $member->addRole($this);
        }

        return $this;
    }

    public function removeMember(Member $member): static
    {
        if ($this->members->removeElement($member)) {
            $member->removeRole($this);
        }

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
