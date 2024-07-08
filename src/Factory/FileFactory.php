<?php

namespace App\Factory;

use App\Entity\File;
use App\Entity\Folder;
use App\Service\FileSizeService;
use App\Service\StorageItem\FilePreviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileFactory
{
    public function __construct(
        private readonly string $workspacePath,
        private readonly ValidatorInterface $validator,
        private readonly FilePreviewService $filePreviewService,
        private readonly Filesystem $filesystem,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileSizeService $fileSizeService
    ) {
    }

    public function createFile(string $name, string $mime, Folder $targetFolder): File
    {
        $file = new File();
        $file->setName($name)
            ->setMime($mime)
            ->setWorkspace($targetFolder->getWorkspace())
            ->setFolder($targetFolder)
            ->updateVersion();

        if (count($errors = $this->validator->validate($file)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->fileSizeService->assertWorkspaceSizeCapacity($file->getWorkspace(), 4000);

        $this->filesystem->touch($this->workspacePath . '/' . $file->getPath());

        $this->entityManager->persist($file);

        return $file;
    }

    public function createFileFromUpload(UploadedFile $uploadedFile, Folder $targetFolder): File
    {
        $name = $uploadedFile->getClientOriginalName();
        $content = file_get_contents($uploadedFile->getPathname());
        $mime = $uploadedFile->getMimeType() ?? $uploadedFile->getClientMimeType();

        $file = $this->createFile($name, $mime, $targetFolder);

        $this->fileSizeService->assertWorkspaceSizeCapacity(
            $file->getWorkspace(),
            FileSizeService::getUploadedFileSize($uploadedFile)
        );

        $this->filesystem->dumpFile($this->workspacePath . '/' . $file->getPath(), $content);
        $this->filePreviewService->generateFilePreview($file);

        return $file;
    }
}
