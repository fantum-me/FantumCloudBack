<?php

namespace App\Service\StorageItem;

use App\Entity\File;
use App\Utils\DocumentConverter;
use Exception;
use Imagick;
use ImagickException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;

class FilePreviewService
{
    // Applies to longest side of image
    const PREVIEW_WIDTH = 500;
    const PREVIEW_FORMAT = "webp";

    public function __construct(
        private readonly string          $workspacePath,
        private readonly LoggerInterface $logger,
        private readonly Filesystem      $filesystem
    )
    {
    }

    public function generateFilePreview(File $file): void
    {
        try {
            $filePath = $this->workspacePath . "/" . $file->getPath();
            $previewPath = $this->workspacePath . "/" . $file->getPreviewPath();

            if (str_starts_with($file->getMime(), 'image')) $this->generateThumbnail($filePath, $previewPath);
            elseif ($file->getMime() === "application/pdf") $this->generateThumbnail($filePath . "[0]", $previewPath);
            elseif (DocumentConverter::isMimeTypeValid($file->getMime())) $this->documentToThumbnail($filePath, $previewPath);

        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    public static function isTypePreviewable(string $type): bool
    {
        return str_starts_with($type, 'image')
            || $type === "application/pdf"
            || DocumentConverter::isMimeTypeValid($type);
    }

    /**
     * @throws ImagickException
     */
    private function generateThumbnail(string $imagePath, string $thumbnailPath): void
    {
        $imagick = new Imagick($imagePath);
        $imagick->thumbnailImage(self::PREVIEW_WIDTH, 0);
        $imagick->setImageFormat(self::PREVIEW_FORMAT);
        $imagick->setImageCompressionQuality(75);
        $imagick->writeImage($thumbnailPath);
        $imagick->clear();
        $imagick->destroy();
    }

    /**
     * @throws ImagickException
     */
    private function documentToThumbnail(string $filePath, string $thumbnailPath): void
    {
        $tempPath = sys_get_temp_dir() . "/" . Uuid::v4()->toRfc4122() . ".pdf";
        DocumentConverter::convertToPdf($filePath, $tempPath);
        $this->generateThumbnail($tempPath . "[0]", $thumbnailPath);
        $this->filesystem->remove($tempPath);
    }
}
