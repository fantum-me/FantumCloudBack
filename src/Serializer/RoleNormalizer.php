<?php

namespace App\Serializer;

use App\Entity\Role;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class RoleNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $data["workspace_id"] = $object->getWorkspace()->getId();
        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Role;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Role::class => true,
        ];
    }
}
