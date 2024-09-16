<?php

namespace App\Domain\Workspace\Controller;

use App\Domain\Invite\InviteRepository;
use App\Domain\Member\MemberFactory;
use App\Domain\User\User;
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
        MemberFactory          $memberFactory,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $code = RequestHandler::getRequestParameter($request, "code", true);
        $invite = $inviteCodeRepository->findOneBy(["code" => $code]);
        if (!$invite) {
            throw new BadRequestHttpException("code is invalid");
        }

        $workspace = $invite->getWorkspace();
        $memberFactory->getOrCreateMember($user, $workspace);
        $entityManager->flush();

        return $this->json($workspace);
    }
}
