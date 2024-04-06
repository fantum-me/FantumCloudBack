<?php

namespace App\Service\ObjectMaker;

use App\Entity\Invite;

class InviteCodeObjectService
{
    public function __construct(
        private readonly MemberObjectService $userObjectService
    )
    {
    }

    public function getInviteCodeObject(Invite $inviteCode): array
    {
        return [
            "code" => $inviteCode->getCode(),
            "created_by" => $this->userObjectService->getMemberObject($inviteCode->getCreatedBy()),
            "use_count" => sizeof($inviteCode->getUsers())
        ];
    }
}
