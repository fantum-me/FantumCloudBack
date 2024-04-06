<?php

namespace App\Controller\Api\Workspace;

use App\Entity\Member;
use App\Entity\User;
use App\Repository\InviteRepository;
use App\Service\ObjectMaker\WorkspaceObjectService;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class JoinWorkspaceController extends AbstractController
{
    #[Route("/api/workspaces/join", name: "api_workspaces_join", methods: "POST")]
    public function join(
        Request                $request,
        #[CurrentUser] User    $user,
        InviteRepository       $inviteCodeRepository,
        EntityManagerInterface $entityManager,
        WorkspaceObjectService $workspaceObjectService
    ): JsonResponse
    {
        $code = RequestHandler::getRequestParameter($request, "code", true);
        $invite = $inviteCodeRepository->findOneBy(["code" => $code]);
        if (!$invite) throw new BadRequestHttpException("code is invalid");

        $member = new Member();
        $member->setUser($user)->setWorkspace($invite->getWorkspace());

        $entityManager->persist($member);
        $entityManager->flush();

        return $this->json($workspaceObjectService->getWorkspaceObject($invite->getWorkspace()));
    }
}
