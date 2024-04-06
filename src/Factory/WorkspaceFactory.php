<?php

namespace App\Factory;

use App\Entity\Member;
use App\Entity\User;
use App\Entity\Workspace;
use App\Security\Permission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WorkspaceFactory
{
    public function __construct(
        private readonly FolderFactory          $folderFactory,
        private readonly RoleFactory            $roleFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface     $validator,
        private readonly string $workspacePath,
        private readonly Filesystem $filesystem
    )
    {
    }

    public function createWorkspace(User $user, string $name, ?int $quota = null): Workspace
    {
        $workspace = new Workspace();
        $workspace->setName($name)->setQuota($quota);

        $defaultRole = $this->roleFactory->createRole("default", 0, $workspace, [
            Permission::READ => true,
            Permission::WRITE => true,
            Permission::TRASH => true,
            Permission::DELETE => false,
            Permission::EDIT_PERMISSIONS => false
        ], true);

        $member = new Member();
        $member->setUser($user)
            ->setIsOwner(true)
            ->addRole($defaultRole);
        $workspace->addMember($member);

        $this->filesystem->mkdir($this->workspacePath . "/" . $workspace->getId());

        $this->folderFactory->createFolder($name, $workspace);

        if (count($errors = $this->validator->validate($workspace)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->entityManager->persist($member);
        $this->entityManager->persist($workspace);
        return $workspace;
    }
}
