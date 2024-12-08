<?php

namespace App\Domain\DataTable\Entity;

use App\Domain\DataTable\Repository\TableValueRepository;
use App\Entity\Abstract\AbstractUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: TableValueRepository::class)]
class TableValue extends AbstractUid
{
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["default"])]
    private ?string $value = null;

    #[ORM\ManyToOne(inversedBy: 'tableValues')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    #[SerializedName("field")]
    private ?TableField $relatedField = null;

    #[ORM\ManyToOne(inversedBy: 'relatedValues')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?TableRecord $record = null;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getRelatedField(): ?TableField
    {
        return $this->relatedField;
    }

    public function setRelatedField(?TableField $relatedField): static
    {
        $this->relatedField = $relatedField;

        return $this;
    }

    public function getRecord(): ?TableRecord
    {
        return $this->record;
    }

    public function setRecord(?TableRecord $record): static
    {
        $this->record = $record;

        return $this;
    }
}
