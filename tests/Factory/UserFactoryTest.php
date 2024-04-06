<?php

namespace App\Tests\Factory;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserFactoryTest extends TestCase
{
    private UserFactory $userFactory;
    private ValidatorInterface $validator;
    private UserRepository $repository;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->repository = $this->createMock(UserRepository::class);

        $this->userFactory = new UserFactory(
            $this->repository,
            $this->validator,
            $this->createMock(EntityManagerInterface::class)
        );
    }

    public function testGetUserWithUpdatedFields(): void
    {
        $id = uniqid();
        $defaultUser = new User($id);
        $defaultUser->setName("default name")
            ->setEmail("default@domain.com");

        $newName = "Michel";
        $newEmail = "michel@domain.com";

        $this->repository->expects($this->once())
            ->method('find')
            ->willReturn($defaultUser);

        $user = $this->userFactory->getOrCreateUser($id, $newName, $newEmail);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($id, $user->getId());
        $this->assertEquals($newName, $user->getName());
        $this->assertEquals($newEmail, $user->getEmail());
    }

    public function testCreateValidUser(): void
    {
        $id = uniqid();
        $name = "Michel";
        $email = "michel@domain.com";

        $user = $this->userFactory->getOrCreateUser($id, $name, $email);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($id, $user->getId());
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($email, $user->getEmail());
    }

    public function testCreateInvalidUser(): void
    {
        $violation = new ConstraintViolation('Validation error message', null, [], null, null, null);
        $violationList = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Validation error message');

        $this->userFactory->getOrCreateUser(uniqid(), "Michel", "michel@domain.com");
    }
}
