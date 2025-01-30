<?php

namespace App\Tests\Unit\Domain\StorageItem\Service;

use App\Domain\File\File;
use App\Domain\Folder\Folder;
use App\Domain\StorageItem\Service\StorageItemDeleteService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class StorageItemDeleteServiceTest extends TestCase
{
    private string $workspacePath = '/path/to/workspace';
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private StorageItemDeleteService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->service = new StorageItemDeleteService(
            $this->workspacePath,
            $this->entityManager,
            $this->filesystem
        );
    }

    public function testDeleteFileWithFile(): void
    {
        $path = 'file.txt';
        $preview = 'preview/file.txt';

        $file = $this->createMock(File::class);
        $file->expects($this->once())
            ->method('getPath')
            ->willReturn($path);
        $file->expects($this->once())
            ->method('getPreviewPath')
            ->willReturn($preview);

        $this->filesystem->expects($this->exactly(2))
            ->method('remove')
            ->with($this->logicalOr(
                $this->workspacePath . '/' . $path,
                $this->workspacePath . '/' . $preview,
            ));

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($file);

        $this->service->deleteStorageItem($file);
    }

    public function testDeleteFileWithoutFile(): void
    {
        $file = $this->createMock(File::class);

        $this->filesystem->expects($this->never())
            ->method('remove');

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($file);

        $this->service->deleteStorageItem($file, false);
    }

    public function testDeleteFolderWithFile(): void
    {
        $path = 'folder';

        $folder = $this->createMock(Folder::class);
        $folder->expects($this->once())
            ->method('getPath')
            ->willReturn($path);

        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with($this->workspacePath . '/' . $path);

        $folder->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection());

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($folder);

        $this->service->deleteStorageItem($folder);
    }

    public function testDeleteFolderWithChildren(): void
    {
        $path = 'folder';

        $folder = $this->createMock(Folder::class);
        $folder->expects($this->once())
            ->method('getPath')
            ->willReturn($path);

        $childFile = $this->createMock(File::class);
        $childFolder = $this->createMock(Folder::class);

        $folder->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$childFile, $childFolder]));

        $childFolder->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection());

        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with($this->workspacePath . '/' . $path);

        $this->entityManager->expects($this->exactly(3))
            ->method('remove')
            ->with($this->logicalOr($childFile, $childFolder, $folder));

        $this->service->deleteStorageItem($folder);
    }
}
