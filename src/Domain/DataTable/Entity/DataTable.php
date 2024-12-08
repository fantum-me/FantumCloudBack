<?php

namespace App\Domain\DataTable\Entity;

use App\Domain\DataTable\DataTableViewType;
use App\Domain\DataTable\Repository\DataTableRepository;
use App\Domain\StorageItem\StorageItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DataTableRepository::class)]
class DataTable extends StorageItem
{
    /**
     * @var Collection<int, TableField>
     */
    #[ORM\OneToMany(mappedBy: 'dataTable', targetEntity: TableField::class, orphanRemoval: true)]
    #[Groups(["datatable_details"])]
    private Collection $fields;

    /**
     * @var Collection<int, TableRecord>
     */
    #[ORM\OneToMany(mappedBy: 'dataTable', targetEntity: TableRecord::class, orphanRemoval: true)]
    #[Groups(["datatable_details"])]
    private Collection $records;

    #[ORM\Column(type: 'json')]
    #[Groups(["default"])]
    private array $views = [];

    public function __construct()
    {
        parent::__construct();
        $this->fields = new ArrayCollection();
        $this->records = new ArrayCollection();
    }

    /**
     * @return Collection<int, TableField>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(TableField $field): static
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
            $field->setDataTable($this);
        }

        return $this;
    }

    public function removeField(TableField $field): static
    {
        if ($this->fields->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getDataTable() === $this) {
                $field->setDataTable(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TableRecord>
     */
    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function addRecord(TableRecord $record): static
    {
        if (!$this->records->contains($record)) {
            $this->records->add($record);
            $record->setDataTable($this);
        }

        return $this;
    }

    public function removeRecord(TableRecord $record): static
    {
        if ($this->records->removeElement($record)) {
            // set the owning side to null (unless already changed)
            if ($record->getDataTable() === $this) {
                $record->setDataTable(null);
            }
        }

        return $this;
    }

    public function getViews(): array
    {
        return array_map(fn($view) => DataTableViewType::from($view), $this->views);
    }

    public function setViews(DataTableViewType ...$views): static
    {
        $this->views = array_unique(array_map(fn($view) => $view->value, $views));

        return $this;
    }
}
