<?php

namespace App\Tests\Unit\Security;

use App\Domain\User\User;
use App\Domain\User\UserFactory;
use App\Security\AccessTokenHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AccessTokenHandlerTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private UserFactory $userFactory;
    private EntityManagerInterface $entityManager;
    private AccessTokenHandler $accessTokenHandler;
    private string $apiEndpoint = 'https://api.example.com';

    protected function setUp(): void
    {
        // Mock dependencies
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->userFactory = $this->createMock(UserFactory::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Instantiate the AccessTokenHandler with the mocks
        $this->accessTokenHandler = new AccessTokenHandler(
            $this->httpClient,
            $this->userFactory,
            $this->apiEndpoint,
            $this->entityManager
        );
    }

    public function testGetUserBadgeFromSuccess()
    {
        $id = '123';
        $name = 'John Doe';
        $email = 'john.doe@example.com';

        // Mock the HTTP response
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn(['id' => $id, 'name' => $name, 'email' => $email,]);

        // Mock the HttpClient to return the mock response
        $this->httpClient
            ->method('request')
            ->with('GET', $this->apiEndpoint . '/me', [
                'headers' => [
                    'Authorization' => 'Bearer valid_token',
                ],
            ])
            ->willReturn($response);

        $user = new User("123");
        $this->userFactory
            ->method('getOrCreateUser')
            ->with($id, $name, $email)
            ->willReturn($user);

        $this->entityManager->expects($this->once())->method('flush');

        $userBadge = $this->accessTokenHandler->getUserBadgeFrom('valid_token');

        $this->assertInstanceOf(UserBadge::class, $userBadge);
        $this->assertEquals($id, $userBadge->getUserIdentifier());
    }

    public function testGetUserBadgeFromInvalidToken()
    {
        // Mock the HTTP response for an invalid token
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401); // Unauthorized

        // Mock the HttpClient to return the mock response
        $this->httpClient
            ->method('request')
            ->with('GET', $this->apiEndpoint . '/me', [
                'headers' => [
                    'Authorization' => 'Bearer invalid_token',
                ],
            ])
            ->willReturn($response);

        // Expect the method to throw a BadCredentialsException
        $this->expectException(BadCredentialsException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        // Call the method to test, which should throw the exception
        $this->accessTokenHandler->getUserBadgeFrom('invalid_token');
    }
}
