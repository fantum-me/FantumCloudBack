<?php

namespace App\Domain\DataTable\Serializer;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\StorageItem\StorageItemNormalizer;

class DataTableNormalizer extends StorageItemNormalizer
{
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = parent::normalize($object, $format, $context);

        $data["type"] = "database";

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof DataTable;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DataTable::class => true
        ];
    }
}
