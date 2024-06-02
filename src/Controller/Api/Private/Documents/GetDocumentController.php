<?php

namespace App\Controller\Api\Private\Documents;

use App\Entity\File;
use App\Service\DocumentsApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GetDocumentController extends AbstractController
{
    #[Route('/api/private/documents/{id}', "api_documents_get", methods: "GET")]
    public function get(
        File $file,
        Request $request,
        DocumentsApiService $docsApiService
    ): BinaryFileResponse {
        $docsApiService->assertAuthentication($request);
        $path = $this->getParameter('workspace_path') . "/" . $file->getPath();
        return $this->file($path, $file->getName() . "." . $file->getExt());
    }
}
