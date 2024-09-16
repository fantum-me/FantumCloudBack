<?php

namespace App\Domain\Workspace;

use App\Domain\Folder\FolderFactory;
use App\Domain\Member\MemberFactory;
use App\Domain\Role\RoleFactory;
use App\Domain\User\User;
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
        private readonly MemberFactory          $memberFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface     $validator,
        private readonly string                 $workspacePath,
        private readonly Filesystem             $filesystem
    )
    {
    }

    public function createWorkspace(User $user, string $name, ?int $quota = null): Workspace
    {
        $workspace = new Workspace();
        $workspace->setName($name)->setQuota($quota);

        $this->roleFactory->createRole("default", 0, $workspace, [
            Permission::READ => true,
            Permission::WRITE => true,
            Permission::TRASH => true,
            Permission::DELETE => false,
            Permission::EDIT_PERMISSIONS => false,
            Permission::MANAGE_MEMBERS => false
        ], true);

        $member = $this->memberFactory->getOrCreateMember($user, $workspace);
        $member->setIsOwner(true);

        $this->filesystem->mkdir($this->workspacePath . "/" . $workspace->getId());

        $this->folderFactory->createFolder($name, $workspace);

        if (count($errors = $this->validator->validate($workspace)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->entityManager->persist($workspace);
        return $workspace;
    }
}
