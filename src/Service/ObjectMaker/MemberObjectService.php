<?php

namespace App\Service\ObjectMaker;

use App\Entity\Member;
use App\Entity\User;

class MemberObjectService
{
    public function __construct(
        private readonly string            $avatarEndpoint,
        private readonly RoleObjectService $roleObjectService
    )
    {
    }

    public function getMemberObject(Member $member): array
    {
        $userObject = $this->getUserObject($member->getUser());
        $memberObject = [
            "is_owner" => $member->isOwner(),
            "roles" => []
        ];

        foreach ($member->getRoles() as $role) $memberObject["roles"][] = $this->roleObjectService->getRoleObject($role);

        return array_merge($userObject, $memberObject);
    }

    private function getUserObject(User $user): array
    {
        return [
            "id" => $user->getId(),
            "name" => $user->getName(),
            "email" => $user->getEmail(),
            "avatar" => $this->avatarEndpoint . "/" . $user->getId()
        ];
    }
}
