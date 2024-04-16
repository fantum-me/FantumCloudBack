<?php

namespace App\Serializer;

use App\Entity\User;
use App\Entity\Workspace;
use App\Service\FileSizeService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class WorkspaceNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly Security $security,
        private readonly string $workspacePath,
        private readonly UserAccessNormalizer $userAccessNormalizer
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $user = $this->security->getUser();

        if (isset($context["groups"]) && in_array("workspace_details", $context["groups"])) {
            if ($user instanceof User) {
                $data["is_owner"] = $object->getOwner()->getUser() === $user;
                $data["access"] = $this->userAccessNormalizer->normalize($user, $format, ["resource" => $object]);
            }

            $data["root"] = $object->getRootFolder()->getId();
            $data["used_space"] = FileSizeService::getFolderSize($this->workspacePath . "/" . $object->getId());
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
