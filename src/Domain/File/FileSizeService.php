<?php

namespace App\Domain\File;

use App\Domain\Workspace\Workspace;
use App\Exception\ContentTooLargeHttpException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileSizeService
{
    public function __construct(
        private readonly string $workspacePath
    )
    {
    }

    public static function getItemSize(string $path): int
    {
        return is_dir($path) ? static::getFolderSize($path) : static::getFileSize($path);
    }

    public static function getFolderSize(string $path): int
    {
        $size = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($iterator as $i) {
            $size += $i->getSize();
        }
        return $size;
    }

    public static function getFileSize(string $path): int
    {
        return filesize($path);
    }

    public static function getUploadedFileSize(UploadedFile $uploadedFile): int
    {
        return self::getFileSize($uploadedFile->getRealPath());
    }

    public function assertWorkspaceSizeCapacity(Workspace $workspace, int $deltaBytes): void
    {
        $path = $this->workspacePath . "/" . $workspace->getId();
        $size = self::getFolderSize($path);
        if ($size + $deltaBytes > $workspace->getQuota()) throw new ContentTooLargeHttpException("Workspace quota exceeded");
    }
}
