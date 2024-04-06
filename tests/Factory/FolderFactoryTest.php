<?php

namespace App\Tests\Factory;

use App\Entity\Folder;
use App\Entity\Workspace;
use App\Factory\FolderFactory;
use App\Service\FileSizeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FolderFactoryTest extends TestCase
{
    private FolderFactory $folderFactory;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method("mkdir")->willReturnSelf();

        $fileSizeService = $this->createMock(FileSizeService::class);
        $fileSizeService->method("assertWorkspaceSizeCapacity")->willReturnSelf();

        $this->folderFactory = new FolderFactory(
            "none",
            $this->validator,
            $filesystem,
            $this->createMock(EntityManagerInterface::class),
            $fileSizeService
        );
    }

    public function testCreateValidRootFolder(): void
    {
        $name = "Folder";
        $workspace = new Workspace();

        $folder = $this->folderFactory->createFolder($name, $workspace);

        $this->assertInstanceOf(Folder::class, $folder);
        $this->assertEquals($name, $folder->getName());
        $this->assertEquals($workspace, $folder->getWorkspace());
        $this->assertEquals($folder, $workspace->getRootFolder());
        $this->assertNull($folder->getFolder());
    }

    public function testCreateValidSubFolder(): void
    {
        $name = "Folder";
        $workspace = new Workspace();
        $parent = new Folder();
        $parent->setWorkspace($workspace);

        $folder = $this->folderFactory->createFolder($name, $parent);

        $this->assertInstanceOf(Folder::class, $folder);
        $this->assertEquals($name, $folder->getName());
        $this->assertEquals($workspace, $folder->getWorkspace());
        $this->assertEquals($parent, $folder->getFolder());
    }

    public function testCreateInvalidFolder(): void
    {
        $violation = new ConstraintViolation('Validation error message', null, [], null, null, null);
        $violationList = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Validation error message');

        $this->folderFactory->createFolder("folder", (new Workspace()));
    }
}
