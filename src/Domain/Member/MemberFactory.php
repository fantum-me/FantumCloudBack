<?php

namespace App\Domain\Member;

use App\Domain\User\User;
use App\Domain\Workspace\Workspace;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MemberFactory
{
    public function __construct(
        private readonly ValidatorInterface     $validator,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function getOrCreateMember(User $user, Workspace $workspace): Member
    {
        if ($member = $user->getWorkspaceMember($workspace)) {
            return $member;
        }

        $member = new Member();
        $member->addRole($workspace->getDefaultRole());
        $user->addRelatedMember($member);
        $workspace->addMember($member);

        if (count($errors = $this->validator->validate($member)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->entityManager->persist($member);
        return $member;
    }
}
