<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractUid;
use App\Repository\InviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: InviteRepository::class)]
#[UniqueEntity("code")]
class Invite extends AbstractUid
{
    #[ORM\Column(length: 255, unique: true)]
    private string $code;

    #[ORM\ManyToOne(inversedBy: 'invites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Workspace $workspace = null;

    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $users;

    #[ORM\ManyToOne(inversedBy: 'invites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $createdBy = null;

    public function __construct()
    {
        parent::__construct();
        $this->code = substr(Uuid::v4()->toBase58(), 0, 8);
        $this->users = new ArrayCollection();
    }

    public function getCode(): string
    {
        return $this->code;
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
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function getCreatedBy(): ?Member
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Member $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
