<?php

namespace App\Service;

use App\Entity\Workspace;
use App\Exception\ContentTooLargeHttpException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileSizeService
{
    public function __construct(
        private readonly string $workspacePath
    ) {
    }

    public static function getFileSize(string $path): int
    {
        return filesize($path);
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

    public static function getUploadedFileSize(UploadedFile $uploadedFile): int {
        return self::getFileSize($uploadedFile->getRealPath());
    }

    public function assertWorkspaceSizeCapacity(Workspace $workspace, int $deltaBytes): void
    {
        $path = $this->workspacePath . "/" . $workspace->getId();
        $size = self::getFolderSize($path);
        if ($size + $deltaBytes > $workspace->getQuota()) throw new ContentTooLargeHttpException("Workspace quota exceeded");
    }
}