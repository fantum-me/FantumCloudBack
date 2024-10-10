<?php

namespace App\Tests\Unit\Utils;

use App\Utils\ArrayCollectionIndexHandler;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ArrayCollectionIndexHandlerTest extends TestCase
{
    public function testGetNewIndexedArrayCollection(): void
    {
        // Create a collection with non-sequential keys
        $originalCollection = new ArrayCollection([
            2 => 'apple',
            5 => 'banana',
            9 => 'cherry'
        ]);

        $result = ArrayCollectionIndexHandler::getNewIndexedArrayCollection($originalCollection);

        // Assert that the result is an ArrayCollection
        $this->assertInstanceOf(ArrayCollection::class, $result);

        // Assert that the values are preserved and in the same order
        $this->assertEquals(['apple', 'banana', 'cherry'], $result->toArray());

        // Assert that the keys are reindexed (0-based)
        $this->assertEquals([0, 1, 2], array_keys($result->toArray()));
    }

    public function testGetNewIndexedArrayCollectionWithEmptyCollection(): void
    {
        $emptyCollection = new ArrayCollection();

        $result = ArrayCollectionIndexHandler::getNewIndexedArrayCollection($emptyCollection);

        // Assert that the result is an empty ArrayCollection
        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertEmpty($result);
    }
}
