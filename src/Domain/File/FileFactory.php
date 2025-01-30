<?php

namespace App\Domain\File;

use App\Domain\File\Service\FilePreviewService;
use App\Domain\Folder\Folder;
use App\Domain\StorageItem\StorageItem;
use App\Domain\StorageItem\StorageItemFactoryInterface;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileFactory implements StorageItemFactoryInterface
{
    public function __construct(
        private readonly string                 $workspacePath,
        private readonly ValidatorInterface     $validator,
        private readonly FilePreviewService     $filePreviewService,
        private readonly Filesystem             $filesystem,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileSizeService        $fileSizeService
    )
    {
    }

    public function handleInsertRequest(Request $request, string $name, Folder $parent): StorageItem
    {
        $mime = RequestHandler::getRequestParameter($request, "mime", true);
        return $this->createFile($name, $mime, $parent);
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

    public function getSupportedTypes(): array
    {
        return [File::class];
    }
}
