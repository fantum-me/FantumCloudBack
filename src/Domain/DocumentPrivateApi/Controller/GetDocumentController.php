<?php

namespace App\Domain\DocumentPrivateApi\Controller;

use App\Domain\DocumentPrivateApi\DocumentsApiService;
use App\Domain\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GetDocumentController extends AbstractController
{
    #[Route('/api/private/documents/{id}', "api_documents_get", methods: "GET")]
    public function get(
        File                $file,
        Request             $request,
        DocumentsApiService $docsApiService
    ): BinaryFileResponse
    {
        $docsApiService->assertAuthentication($request);
        $path = $this->getParameter('workspace_path') . "/" . $file->getPath();
        return $this->file($path, $file->getName());
    }
}
