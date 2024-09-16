<?php

namespace App\Security;

use App\Domain\User\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly HttpClientInterface    $httpClient,
        private readonly UserFactory            $userMakerService,
        private readonly string                 $apiEndpoint,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $response = $this->httpClient->request(
            'GET',
            $this->apiEndpoint . "/me",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        $data = $response->toArray();

        $user = $this->userMakerService->getOrCreateUser($data["id"], $data["name"], $data["email"]);
        $this->entityManager->flush();

        return new UserBadge($user->getUserIdentifier());
    }
}
