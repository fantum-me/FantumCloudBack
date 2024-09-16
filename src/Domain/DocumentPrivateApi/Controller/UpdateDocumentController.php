<?php

namespace App\Domain\DocumentPrivateApi\Controller;

use App\Domain\DocumentPrivateApi\DocumentsApiService;
use App\Domain\File\File;
use App\Domain\File\FileSizeService;
use App\Domain\File\Service\FilePreviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class UpdateDocumentController extends AbstractController
{
    #[Route('/api/private/documents/{id}', "api_documents_update", methods: "POST")]
    public function update(
        File                   $file,
        Request                $request,
        DocumentsApiService    $docsApiService,
        FilePreviewService     $filePreviewService,
        EntityManagerInterface $entityManager,
        FileSizeService        $fileSizeService
    ): Response
    {
        $docsApiService->assertAuthentication($request);

        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException("file not found");
        }

        $path = $this->getParameter('workspace_path') . "/" . $file->getFolder()->getPath();

        if (!$uploadedFile->isValid()) {
            throw new BadRequestHttpException("Invalid file");
        }

        $fileSizeService->assertWorkspaceSizeCapacity(
            $file->getWorkspace(),
            FileSizeService::getUploadedFileSize($uploadedFile)
        );

        $uploadedFile->move($path, $file->getFullSystemFileName());
        $filePreviewService->generateFilePreview($file);

        $file->setUpdatedAtValue();
        $file->updateVersion();
        $entityManager->flush();

        return new Response("done");
    }
}
