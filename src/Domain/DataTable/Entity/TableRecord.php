<?php

namespace App\Domain\DataTable\Entity;

use App\Domain\DataTable\Repository\TableRecordRepository;
use App\Entity\Abstract\AbstractUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: TableRecordRepository::class)]
class TableRecord extends AbstractUid
{
    /**
     * @var Collection<int, TableValue>
     */
    #[ORM\OneToMany(mappedBy: 'record', targetEntity: TableValue::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $relatedValues;

    #[ORM\ManyToOne(inversedBy: 'records')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?DataTable $dataTable = null;

    public function __construct()
    {
        parent::__construct();
        $this->relatedValues = new ArrayCollection();
    }

    /**
     * @return Collection<int, TableValue>
     */
    public function getRelatedValues(): Collection
    {
        return $this->relatedValues;
    }

    public function addRelatedValue(TableValue $relatedValue): static
    {
        if (!$this->relatedValues->contains($relatedValue)) {
            $this->relatedValues->add($relatedValue);
            $relatedValue->setRecord($this);
        }

        return $this;
    }

    public function removeRelatedValue(TableValue $relatedValue): static
    {
        if ($this->relatedValues->removeElement($relatedValue)) {
            // set the owning side to null (unless already changed)
            if ($relatedValue->getRecord() === $this) {
                $relatedValue->setRecord(null);
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
}
