<?php

namespace App\Domain\Folder;

use App\Domain\File\FileSizeService;
use App\Domain\Workspace\Workspace;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FolderFactory
{
    public function __construct(
        private readonly string                 $workspacePath,
        private readonly ValidatorInterface     $validator,
        private readonly Filesystem             $filesystem,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileSizeService        $fileSizeService
    )
    {
    }

    public function createFolder(string $name, Folder|Workspace $parent): Folder
    {
        $workspace = $parent instanceof Folder ? $parent->getWorkspace() : $parent;

        $folder = new Folder();
        $folder->setName($name)
            ->setWorkspace($workspace)
            ->setFolder($parent instanceof Folder ? $parent : null);

        if ($parent instanceof Workspace) {
            $parent->setRootFolder($folder);
        }

        if (count($errors = $this->validator->validate($folder)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->fileSizeService->assertWorkspaceSizeCapacity($workspace, 4000);

        $this->filesystem->mkdir($this->workspacePath . '/' . $folder->getPath());

        $this->entityManager->persist($folder);

        return $folder;
    }
}
