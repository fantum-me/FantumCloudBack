<?php

namespace App\Tests\Factory;

use App\Entity\Folder;
use App\Entity\Member;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Workspace;
use App\Factory\FolderFactory;
use App\Factory\RoleFactory;
use App\Factory\WorkspaceFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WorkspaceFactoryTest extends TestCase
{
    private WorkspaceFactory $workspaceFactory;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);

        $folderFactory = $this->createMock(FolderFactory::class);
        $folderFactory->method("createFolder")->willReturn(new Folder());

        $roleFactory = $this->createMock(RoleFactory::class);
        $roleFactory->method("createRole")->willReturn(new Role());

        $this->workspaceFactory = new WorkspaceFactory(
            $folderFactory,
            $roleFactory,
            $this->createMock(EntityManagerInterface::class),
            $this->validator
        );
    }

    public function testCreateValidWorkspace(): void
    {
        $user = new User(uniqid());
        $name = "Workspace";
        $quota = random_int(10, 10000000000);

        $workspace = $this->workspaceFactory->createWorkspace($user, $name, $quota);

        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertEquals($name, $workspace->getName());
        $this->assertEquals($quota, $workspace->getQuota());

        $members = $workspace->getMembers();
        $this->assertCount(1, $members);


        $member = $members->first();
        $this->assertInstanceOf(Member::class, $member);
        $this->assertEquals($user, $member->getUser());
        $this->assertTrue($member->isOwner());
    }

    public function testCreateInvalidWorkspace(): void
    {
        $violation = new ConstraintViolation('Validation error message', null, [], null, null, null);
        $violationList = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Validation error message');

        $this->workspaceFactory->createWorkspace(new User(uniqid()), "Workspace");
    }
}
