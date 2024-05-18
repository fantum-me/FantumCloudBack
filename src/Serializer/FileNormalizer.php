<?php

namespace App\Serializer;

use App\Entity\File;
use App\Entity\User;
use App\Service\FileSizeService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class FileNormalizer implements NormalizerInterface
{

    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly Security $security,
        private readonly UserAccessNormalizer $userAccessNormalizer,
        private readonly string $workspacePath,
        private readonly Filesystem $filesystem
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $data["access"] = $this->userAccessNormalizer->normalize($user, $format, ["resource" => $object]);
        }
        $data["workspace_id"] = $object->getWorkspace()->getId();

        $data["has_preview"] = $this->filesystem->exists($this->workspacePath . "/" . $object->getPreviewPath());
        $data["size"] = FileSizeService::getFileSize($this->workspacePath . "/" . $object->getPath());
        $data["parent_id"] = $object->getFolder()->getId();

        if (str_starts_with($object->getType(), 'image')) {
            $dimension = getimagesize($this->workspacePath . "/" . $object->getPath());
            $data["width"] = $dimension[0];
            $data["height"] = $dimension[1];
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof File;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            File::class => true,
        ];
    }
}
