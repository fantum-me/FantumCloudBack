<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Security\AccessTokenHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandlerTest extends TestCase
{
    private MockHttpClient $httpClient;
    private UserFactory $userFactory;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->httpClient = new MockHttpClient();
        $this->userFactory = $this->createMock(UserFactory::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager->method("flush")->willReturnSelf();
    }

    public function testGetUserBadgeFromValidAccessToken(): void
    {
        $accessToken = uniqid();

        $userData = [
            'id' => uniqid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $user = new User($userData["id"]);

        $mockResponse = new MockResponse(json_encode($userData), ['http_code' => 200]);
        $this->httpClient->setResponseFactory($mockResponse);

        $this->userFactory->expects($this->once())
            ->method('getOrCreateUser')
            ->with($userData['id'], $userData['name'], $userData['email'])
            ->willReturn($user);

        $accessTokenHandler = new AccessTokenHandler(
            $this->httpClient,
            $this->userFactory,
            'https://example.com/api',
            $this->entityManager
        );

        $userBadge = $accessTokenHandler->getUserBadgeFrom($accessToken);

        $this->assertInstanceOf(UserBadge::class, $userBadge);
        $this->assertSame($userData["id"], $userBadge->getUserIdentifier());
    }

    public function testGetUserBadgeFromInvalidAccessToken(): void
    {
        $accessToken = uniqid();

        $mockResponse = new MockResponse([], ['http_code' => 401]);
        $this->httpClient->setResponseFactory($mockResponse);

        $this->expectException(BadCredentialsException::class);

        $accessTokenHandler = new AccessTokenHandler(
            $this->httpClient,
            $this->userFactory,
            'https://example.com/api',
            $this->entityManager
        );

        $accessTokenHandler->getUserBadgeFrom($accessToken);
    }
}
