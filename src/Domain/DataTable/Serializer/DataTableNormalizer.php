<?php

namespace App\Domain\DataTable\Serializer;

use App\Domain\AccessControl\UserAccessNormalizer;
use App\Domain\DataTable\Entity\DataTable;
use App\Domain\StorageItem\StorageItemNormalizer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DataTableNormalizer extends StorageItemNormalizer
{
    public function __construct(
        ObjectNormalizer              $objectNormalizer,
        Security                      $security,
        UserAccessNormalizer          $userAccessNormalizer,
        private readonly HubInterface $mercureHub,
    )
    {
        parent::__construct($objectNormalizer, $security, $userAccessNormalizer);
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = parent::normalize($object, $format, $context);

        $data["type"] = "database";

        $data["update_topic"] = "database-update/" . $object->getId()->toRfc4122();
        $data["update_url"] = $this->mercureHub->getUrl() . "?topic=" . $data["update_topic"];
        $data["update_token"] = $this->mercureHub->getFactory()->create([$data["update_topic"]]);

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
