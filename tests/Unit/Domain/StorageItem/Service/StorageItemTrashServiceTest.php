<?php

namespace App\Tests\Unit\Domain\StorageItem\Service;

use App\Domain\File\File;
use App\Domain\Folder\Folder;
use App\Domain\StorageItem\Service\StorageItemTrashService;
use App\Domain\StorageItem\StorageItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class StorageItemTrashServiceTest extends TestCase
{
    public function testTrashItem(): void
    {
        $item = new File();

        StorageItemTrashService::trashItem($item);

        $this->assertTrue($item->isInTrash());
        $this->assertEquals(1, $item->getVersion());
    }

    public function testTrashItemAlreadyInTrash(): void
    {
        $item = new File();
        $item->setInTrash(true);

        StorageItemTrashService::trashItem($item);

        $this->assertEquals(0, $item->getVersion());
    }

    public function testTrashItemWithParentInTrash(): void
    {
        $item = new File();

        $parent = new Folder();
        $parent->setInTrash(true);
        $parent->addItem($item);

        StorageItemTrashService::trashItem($item);

        $this->assertEquals(0, $item->getVersion());
        $this->assertFalse($item->isInTrash());

        $this->assertEquals(0, $parent->getVersion());
        $this->assertTrue($parent->isInTrash());
    }

    public function testTrashFolder(): void
    {
        $fileChild = new Folder();
        $fileChild->setInTrash(true);

        $folderChild = new File();
        $folderChild->setInTrash(false);

        $parent = new Folder();
        $parent->addItem($fileChild);
        $parent->addItem($folderChild);

        StorageItemTrashService::trashItem($parent);

        $this->assertEquals(1, $fileChild->getVersion());
        $this->assertEquals(0, $folderChild->getVersion());
        $this->assertGreaterThanOrEqual(1, $parent->getVersion());
        $this->assertTrue($parent->isInTrash());
    }

    public function testTrashNestedFolder(): void
    {
        $grandchildItem = $this->createMock(StorageItemInterface::class);
        $grandchildItem->expects($this->once())->method('setInTrash')->with(false);
        $grandchildItem->expects($this->once())->method('updateVersion');
        $grandchildItem->method('isInTrash')->willReturn(true);

        $childFolder = $this->createMock(Folder::class);
        $childFolder->expects($this->never())->method('setInTrash');
        $childFolder->expects($this->never())->method('updateVersion');
        $childFolder->method('isInTrash')->willReturn(false);
        $childFolder->method('getItems')->willReturn(new ArrayCollection([$grandchildItem]));

        $parentFolder = $this->createMock(Folder::class);
        $parentFolder->expects($this->once())->method('setInTrash')->with(true);
        $parentFolder->expects($this->once())->method('updateVersion');
        $parentFolder->method('isInTrash')->willReturn(false);
        $parentFolder->method('getFolder')->willReturn(null);
        $parentFolder->method('getItems')->willReturn(new ArrayCollection([$childFolder]));

        StorageItemTrashService::trashItem($parentFolder);
    }
}
