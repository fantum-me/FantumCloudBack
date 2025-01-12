<?php

namespace App\Domain\DataTable\Entity;

use App\Domain\DataTable\DataTableViewType;
use App\Domain\DataTable\Repository\DataViewRepository;
use App\Domain\DataTable\Validator\ValidViewSettings;
use App\Domain\Member\Member;
use App\Entity\Abstract\AbstractUid;
use App\Entity\Trait\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity(repositoryClass: DataViewRepository::class)]
#[ORM\HasLifecycleCallbacks]
class DataView extends AbstractUid
{
    use TimestampTrait;

    #[ORM\Column(length: 255)]
    #[Groups(["default"])]
    private ?string $name = null;

    #[ORM\Column(length: 15)]
    #[Groups(["default"])]
    private ?string $type = null;

    #[ORM\Column(type: Types::JSON)]
    // Check getSerializedFieldSettings()
    #[Ignore]
    private array $fieldSettings;

    #[ORM\ManyToOne(inversedBy: 'views')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?DataTable $dataTable = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["default"])]
    private ?Member $createdBy = null;

    public function __construct()
    {
        parent::__construct();
        $this->fieldSettings = [];
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(DataTableViewType $type): static
    {
        $this->type = $type->value;

        return $this;
    }

    public function getFieldSettings(): array
    {
        return $this->fieldSettings;
    }

    public function setFieldSettings(array $fieldSettings): static
    {
        $this->fieldSettings = $fieldSettings;

        return $this;
    }

    #[Groups(["default"])]
    #[SerializedName("field_settings")]
    #[Context([AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true])]
    public function getSerializedFieldSettings(): object|array
    {
        // This ensure empty array serialized as json object
        if (empty($this->fieldSettings)) return (object)[];
        else return $this->fieldSettings;
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
