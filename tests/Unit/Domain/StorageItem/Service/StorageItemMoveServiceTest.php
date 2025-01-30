<?php

namespace App\Tests\Unit\Domain\StorageItem\Service;

use App\Domain\File\File;
use App\Domain\Folder\Folder;
use App\Domain\StorageItem\Service\StorageItemMoveService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class StorageItemMoveServiceTest extends TestCase
{
    private string $workspacePath = '/path/to/workspace';
    private StorageItemMoveService $service;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->service = new StorageItemMoveService($this->workspacePath, $this->filesystem);
    }

    public function testMoveFile(): void
    {
        $basePath = "base_path/file.txt";
        $targetPath = "target_path/file.txt";

        $basePreviewPath = "base_path/preview.txt";
        $targetPreviewPath = "target_path/preview.txt";

        $targetFolder = $this->createMock(Folder::class);

        $item = $this->createMock(File::class);

        $item->expects($this->exactly(2))
            ->method('getPath')
            ->willReturnOnConsecutiveCalls($basePath, $targetPath);

        $item->expects($this->exactly(2))
            ->method('getPreviewPath')
            ->willReturnOnConsecutiveCalls($basePreviewPath, $targetPreviewPath);

        $item->expects($this->once())->method('setFolder')->with($targetFolder);

        $this->filesystem->expects($this->once())
            ->method("exists")
            ->with($this->workspacePath . '/' . $basePreviewPath)
            ->willReturn(true);

        $matcher = $this->exactly(2);
        $this->filesystem->expects($matcher)
            ->method('rename')
            ->willReturnCallback(fn($base, $target) => match ([$base, $target]) {
                [
                    $this->workspacePath . '/' . $basePath,
                    $this->workspacePath . '/' . $targetPath
                ], [
                    $this->workspacePath . '/' . $basePreviewPath,
                    $this->workspacePath . '/' . $targetPreviewPath
                ] => null
            });

        $this->service->moveStorageItem($item, $targetFolder);
    }
}
