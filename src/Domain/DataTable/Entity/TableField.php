<?php

namespace App\Domain\DataTable\Entity;

use App\Domain\DataTable\Repository\TableFieldRepository;
use App\Domain\DataTable\Service\TableFieldTypeService;
use App\Domain\DataTable\TableFieldType;
use App\Entity\Abstract\AbstractUid;
use App\Entity\Trait\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity(repositoryClass: TableFieldRepository::class)]
#[ORM\HasLifecycleCallbacks]
class TableField extends AbstractUid
{
    use TimestampTrait;

    #[ORM\Column(length: 255)]
    #[Groups(["default"])]
    private ?string $name = null;

    #[ORM\Column(length: 255, enumType: TableFieldType::class)]
    #[Groups(["default"])]
    private ?TableFieldType $type = null;

    /**
     * @var Collection<int, TableValue>
     */
    #[ORM\OneToMany(mappedBy: 'relatedField', targetEntity: TableValue::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $tableValues;

    #[ORM\ManyToOne(inversedBy: 'fields')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?DataTable $dataTable = null;

    #[ORM\Column(nullable: false)]
    #[Groups(["default"])]
    private bool $isTitle = false;

    #[ORM\Column(type: 'json', nullable: true)]
    // Check getSerializedOptions()
    #[Ignore]
    private ?array $options = null;

    public function __construct()
    {
        parent::__construct();
        $this->tableValues = new ArrayCollection();
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

    public function getType(): ?TableFieldType
    {
        return $this->type;
    }

    public function setType(TableFieldType $type): static
    {
        $this->type = $type;

        return $this;
    }

    #[Groups(["default"])]
    public function getDefaultValue(): string
    {
        return TableFieldTypeService::getDefaultEmptyValue($this);
    }

    /**
     * @return Collection<int, TableValue>
     */
    public function getTableValues(): Collection
    {
        return $this->tableValues;
    }

    public function addTableValue(TableValue $tableValue): static
    {
        if (!$this->tableValues->contains($tableValue)) {
            $this->tableValues->add($tableValue);
            $tableValue->setRelatedField($this);
        }

        return $this;
    }

    public function removeTableValue(TableValue $tableValue): static
    {
        if ($this->tableValues->removeElement($tableValue)) {
            // set the owning side to null (unless already changed)
            if ($tableValue->getRelatedField() === $this) {
                $tableValue->setRelatedField(null);
            }
        }

        return $this;
    }

    public function getDataTable(): ?DataTable
    {
        return $this->dataTable;
    }

    public function setDataTable(?DataTable $dataTable): static
    {
        $this->dataTable = $dataTable;

        return $this;
    }

    public function isTitle(): bool
    {
        return $this->isTitle;
    }

    public function setIsTitle(bool $isTitle): static
    {
        $this->isTitle = $isTitle;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;

        return $this;
    }

    #[Groups(["default"])]
    #[SerializedName("options")]
    #[Context([AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true])]
    public function getSerializedOptions(): object|array
    {
        // This ensure empty array serialized as json object
        if (empty($this->options)) return (object)[];
        else return $this->options;
    }

}
