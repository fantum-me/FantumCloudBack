<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
class Folder extends StorageItem
{
    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: StorageItem::class)]
    #[Ignore]
    private Collection $items;

    public function __construct()
    {
        parent::__construct();
        $this->items = new ArrayCollection();
        $this->accessControls = new ArrayCollection();
    }

    /**
     * @return Collection<int, StorageItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(StorageItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setFolder($this);
        }

        return $this;
    }

    public function removeItem(StorageItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getFolder() === $this) {
                $item->setFolder(null);
            }
        }

        return $this;
    }
}
