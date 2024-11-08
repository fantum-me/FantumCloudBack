<?php

namespace App\Tests\Unit\Domain\StorageItem\Service;

use App\Domain\Folder\Folder;
use App\Domain\StorageItem\Service\StorageItemService;
use App\Domain\StorageItem\StorageItem;
use App\Domain\StorageItem\StorageItemRepository;
use App\Domain\Workspace\Workspace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StorageItemServiceTest extends TestCase
{
    private StorageItemRepository $repository;
    private StorageItemService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(StorageItemRepository::class);
        $this->service = new StorageItemService($this->repository);
    }

    public function testGetStorageItemReturnsItemWhenFound(): void
    {
        $id = 'item123';
        $item = $this->createMock(StorageItem::class);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($item);

        $result = $this->service->getStorageItem($id);

        $this->assertSame($item, $result);
    }

    public function testGetStorageItemThrowsExceptionWhenNotFoundAndRequired(): void
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $this->expectException(BadRequestHttpException::class);

        $this->service->getStorageItem("abc");
    }

    public function testGetStorageItemReturnsNullWhenNotFoundAndNotRequired(): void
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $result = $this->service->getStorageItem("abc", false);

        $this->assertNull($result);
    }

    public function testAssertFolderReturnsFolderWhenItemIsFolder(): void
    {
        $folder = $this->createMock(Folder::class);

        $result = $this->service->assertFolder($folder);

        $this->assertSame($folder, $result);
    }

    public function testAssertFolderThrowsExceptionWhenItemIsNotFolder(): void
    {
        $item = $this->createMock(StorageItem::class);

        $this->expectException(BadRequestHttpException::class);

        $this->service->assertFolder($item);
    }

    public function testAssertInWorkspaceDoesNotThrowExceptionWhenItemIsInWorkspace(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $item = $this->createMock(StorageItem::class);

        $item->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $this->service->assertInWorkspace($workspace, $item);

        $this->addToAssertionCount(1); // Assert that no exception was thrown
    }

    public function testAssertInWorkspaceThrowsExceptionWhenItemIsNotInWorkspace(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $differentWorkspace = $this->createMock(Workspace::class);
        $item = $this->createMock(StorageItem::class);

        $item->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($differentWorkspace);

        $this->expectException(BadRequestHttpException::class);

        $this->service->assertInWorkspace($workspace, $item);
    }
}
