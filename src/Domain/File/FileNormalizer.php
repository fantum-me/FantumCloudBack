<?php

namespace App\Domain\File;

use App\Domain\AccessControl\UserAccessNormalizer;
use App\Domain\StorageItem\StorageItemNormalizer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class FileNormalizer extends StorageItemNormalizer
{
    public function __construct(
        private readonly ObjectNormalizer     $objectNormalizer,
        private readonly Security             $security,
        private readonly UserAccessNormalizer $userAccessNormalizer,
        private readonly string               $workspacePath,
        private readonly Filesystem           $filesystem
    )
    {
        parent::__construct(
            $this->objectNormalizer,
            $this->security,
            $this->userAccessNormalizer
        );
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = parent::normalize($object, $format, $context);

        $path = $this->workspacePath . "/" . $object->getPath();
        $data["size"] = FileSizeService::getItemSize($path);

        $data["type"] = "file";
        $data["mime"] = $object->getMime();
        $data["has_preview"] = $this->filesystem->exists($this->workspacePath . "/" . $object->getPreviewPath());

        if (str_starts_with($object->getMime(), 'image')) {
            $dimension = getimagesize($path);
            if ($dimension) {
                $data["width"] = $dimension[0];
                $data["height"] = $dimension[1];
            }
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof File;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            File::class => true
        ];
    }
}
