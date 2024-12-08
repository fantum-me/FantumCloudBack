<?php

namespace App\Domain\StorageItem;

use App\Domain\AccessControl\UserAccessNormalizer;
use App\Domain\User\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

abstract class StorageItemNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer     $objectNormalizer,
        private readonly Security             $security,
        private readonly UserAccessNormalizer $userAccessNormalizer
    )
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->objectNormalizer->normalize($object, $format, $context);
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $data["access"] = $this->userAccessNormalizer->normalize($user, $format, ["resource" => $object]);
        }

        $data["workspace_id"] = $object->getWorkspace()->getId();
        $data["parent_id"] = $object->getFolder()?->getId();

        if (isset($context["groups"]) && in_array("item_parents", $context["groups"])) {
            $this->addParentsToData($object, $data);
        }

        return $data;
    }

    protected function addParentsToData(StorageItem $item, array &$data): void
    {
        if ($item->isInTrash()) {
            $data["parents"] = [["id" => "trash", "name" => "Trash"]];
            return;
        }

        $parents = [];
        $parent = $item->getFolder();

        while ($parent) {
            $parents[] = [
                "id" => $parent->getId(),
                "name" => $parent->getName(),
                "is_root" => $item->getWorkspace()->getRootFolder() === $parent
            ];

            if ($parent->isInTrash()) {
                $parents[] = ["id" => "trash", "name" => "Trash"];
                break;
            }

            $parent = $parent->getFolder();
        }

        $data["parents"] = array_reverse($parents);
    }
}
