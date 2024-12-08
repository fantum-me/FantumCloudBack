<?php

namespace App\Domain\DataTable\Serializer;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\TableRecord;
use App\Domain\DataTable\Entity\TableValue;
use App\Domain\DataTable\Service\TableFieldTypeService;
use App\Domain\StorageItem\StorageItemNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TableRecordNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $objectNormalizer
    )
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        assert($object instanceof TableRecord);
        $data = $this->objectNormalizer->normalize($object, $format, $context);

        $data['values'] = [];
        foreach ($object->getDataTable()->getFields() as $field) {
            $fieldId = $field->getId()->toRfc4122();

            foreach ($object->getRelatedValues() as $value) {
                if ($value instanceof TableValue && $value->getRelatedField() === $field) {
                    $data['values'][$fieldId] = $value->getValue();
                }
            }
            if (!array_key_exists($fieldId, $data['values'])) {
                $data['values'][$fieldId] = TableFieldTypeService::getDefaultEmptyValue($field);
            }
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TableRecord;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            TableRecord::class => true
        ];
    }
}
