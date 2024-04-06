<?php

namespace App\Controller\Api\Folder;

use App\Entity\Folder;
use App\Entity\User;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ModifyFolderController extends AbstractController
{
    #[Route('/api/folders/{id}', name: 'api_folders_modify', methods: "PATCH")]
    public function modify(
        Request                $request,
        Folder                 $folder,
        #[CurrentUser] User    $user,
        EntityManagerInterface $entityManager,
        PermissionService      $permissionService
    ): Response
    {
        $name = RequestHandler::getRequestParameter($request, "name");

        if ($name) {
            $permissionService->assertPermission($user, Permission::WRITE, $folder);
            $folder->setName($name);
        }

        $folder->updateVersion();
        $entityManager->flush();

        return new Response("done");
    }
}
