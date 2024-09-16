<?php

namespace App\Domain\User;

use App\Domain\Member\Member;
use App\Domain\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    #[Assert\Uuid]
    #[Groups(["default"])]
    private ?string $id;

    #[ORM\Column(length: 255)]
    #[Groups(["default"])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["default"])]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Member::class)]
    #[Ignore]
    private Collection $relatedMembers;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->relatedMembers = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    #[Ignore]
    public function getRoles(): array
    {
        return ["ROLE_USER"];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Member>
     */
    public function getRelatedMembers(): Collection
    {
        return $this->relatedMembers;
    }

    public function addRelatedMember(Member $relatedMember): static
    {
        if (!$this->relatedMembers->contains($relatedMember)) {
            $this->relatedMembers->add($relatedMember);
            $relatedMember->setUser($this);
        }

        return $this;
    }

    public function removeRelatedMember(Member $relatedMember): static
    {
        if ($this->relatedMembers->removeElement($relatedMember)) {
            // set the owning side to null (unless already changed)
            if ($relatedMember->getUser() === $this) {
                $relatedMember->setUser(null);
            }
        }

        return $this;
    }

    #[Ignore]
    public function isInWorkspace(Workspace $workspace): bool
    {
        return !!$this->getWorkspaceMember($workspace);
    }

    #[Ignore]
    public function getWorkspaceMember(Workspace $workspace): ?Member
    {
        foreach ($this->relatedMembers as $member) if ($member->getWorkspace() === $workspace) return $member;
        return null;
    }
}
