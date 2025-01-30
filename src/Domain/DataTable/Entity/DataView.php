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
    #[ValidViewSettings]
    // Check getSerializedFieldSettings()
    #[Ignore]
    private array $settings;

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
        $this->settings = [];
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

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): static
    {
        $this->settings = $settings;

        return $this;
    }

    #[Groups(["default"])]
    #[SerializedName("settings")]
    #[Context([AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true])]
    public function getSerializedSettings(): object|array
    {
        // This ensure empty array serialized as json object
        if (empty($this->settings)) return (object)[];
        else return $this->settings;
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
