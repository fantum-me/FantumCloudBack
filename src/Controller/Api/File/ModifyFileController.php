<?php

namespace App\Controller\Api\File;

use App\Entity\File;
use App\Entity\User;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ModifyFileController extends AbstractController
{
    #[Route('/api/files/{id}', name: 'api_files_modify', methods: "PATCH")]
    public function modify(
        Request $request,
        File $file,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
        PermissionService $permissionService
    ): JsonResponse {
        $name = RequestHandler::getRequestParameter($request, "name", true);

        if ($name) {
            $permissionService->assertPermission($user, Permission::WRITE, $file);
            $file->setName($name);
        }

        $file->updateVersion();
        $entityManager->flush();

        return $this->json($file);
    }
}
