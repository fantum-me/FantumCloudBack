<?php

namespace App\Domain\Folder;

use App\Domain\AccessControl\UserAccessNormalizer;
use App\Domain\File\FileSizeService;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\StorageItemNormalizer;
use App\Security\Permission;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class FolderNormalizer extends StorageItemNormalizer implements NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly ObjectNormalizer             $objectNormalizer,
        private readonly Security                     $security,
        private readonly UserAccessNormalizer         $userAccessNormalizer,
        private readonly string                       $workspacePath,
        private readonly StorageItemPermissionService $itemPermissionService,
        private readonly HubInterface                 $mercureHub,
        private readonly LoggerInterface              $logger
    )
    {
        parent::__construct(
            $this->objectNormalizer,
            $this->security,
            $this->userAccessNormalizer
        );
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $this->logger->info("aaa");
        $data = parent::normalize($object, $format, $context);

        $path = $this->workspacePath . "/" . $object->getPath();
        $data["size"] = FileSizeService::getItemSize($path);

        $data["type"] = "folder";
        $data["is_root"] = $object->getFolder() === null;


        $data["version_update_topic"] = "folder-version/" . $object->getId()->toRfc4122();
        $data["version_update_url"] = $this->mercureHub->getUrl() . "?topic=" . $data["version_update_topic"];
        $data["version_update_token"] = $this->mercureHub->getFactory()->create([$data["version_update_topic"]]);

        if (isset($context["groups"]) && in_array("folder_children", $context["groups"])) {
            $childrenContext = array_merge([], $context);;
            $childrenContext["groups"] = array_diff(
                $context["groups"],
                ["folder_children", "datatable_details", "item_parents"]
            );

            $this->addChildrenToData($object, $data, $format, $childrenContext);
        }

        return $data;
    }

    protected function addChildrenToData(Folder $folder, array &$data, ?string $format, array $context): void
    {
        $user = $this->security->getUser();
        $member = $user?->getWorkspaceMember($folder->getWorkspace());
        if ($user && !$member) {
            return;
        }

        foreach ($folder->getItems() as $item) {
            if (!$item->isInTrash()) {
                if (!$user || $this->itemPermissionService->hasItemPermission($member, Permission::READ, $item)) {
                    $data["items"][] = $this->normalizer->normalize($item, $format, $context);
                }
            }
        }
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Folder;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Folder::class => true
        ];
    }
}
