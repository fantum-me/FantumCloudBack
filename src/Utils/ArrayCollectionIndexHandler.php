<?php

namespace App\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ArrayCollectionIndexHandler
{
    public static function getNewIndexedArrayCollection(Collection $collection): ArrayCollection
    {
        return new ArrayCollection(array_values($collection->toArray()));
    }

}
