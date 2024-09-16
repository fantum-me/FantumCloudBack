<?php

namespace App\Domain\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserFactory
{
    public function __construct(
        private readonly UserRepository         $userRepository,
        private readonly ValidatorInterface     $validator,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function getOrCreateUser(string $id, string $name, string $email): User
    {
        if ($user = $this->userRepository->find($id)) {
            $user->setName($name)
                ->setEmail($email);
            return $user;
        }

        $user = new User($id);
        $user->setName($name)->setEmail($email);

        if (count($errors = $this->validator->validate($user)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->entityManager->persist($user);
        return $user;
    }
}
