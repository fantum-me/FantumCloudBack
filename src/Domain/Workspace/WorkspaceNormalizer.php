<?php

namespace App\Domain\Workspace;

use App\Domain\AccessControl\UserAccessNormalizer;
use App\Domain\File\FileSizeService;
use App\Domain\User\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class WorkspaceNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer     $normalizer,
        private readonly Security             $security,
        private readonly string               $workspacePath,
        private readonly UserAccessNormalizer $userAccessNormalizer
    )
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $user = $this->security->getUser();

        if (isset($context["groups"]) && in_array("workspace_details", $context["groups"])) {
            $data["owner_id"] = $object->getOwner()->getId();
            $data["root"] = $object->getRootFolder()->getId();
            $data["used_space"] = FileSizeService::getFolderSize($this->workspacePath . "/" . $object->getId());

            if ($user instanceof User) {
                $data["member"] = $user->getWorkspaceMember($object);
                $data["owner"] = $data["owner_id"] === $data["member"]->getId();
                $data["access"] = $this->userAccessNormalizer->normalize($user, $format, ["resource" => $object]);
            }
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Workspace;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Workspace::class => true,
        ];
    }
}
