<?php

namespace App\Domain\AccessControl;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AccessControlNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
    )
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $data["role_id"] = $object->getRole()->getId();
        $data["item_id"] = $object->getItem()->getId();
        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AccessControl;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AccessControl::class => true,
        ];
    }
}
