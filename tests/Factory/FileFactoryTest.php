<?php

namespace App\Tests\Factory;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\Workspace;
use App\Factory\FileFactory;
use App\Service\FileSizeService;
use App\Service\StorageItem\FilePreviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileFactoryTest extends WebTestCase
{
    private FileFactory $fileFactory;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->validator = $this->createMock(ValidatorInterface::class);

        $filePreviewService = $this->createMock(FilePreviewService::class);
        $filePreviewService->method("generateFilePreview")->willReturnSelf();

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method("dumpFile")->willReturnSelf();

        $fileSizeService = $this->createMock(FileSizeService::class);
        $fileSizeService->method("assertWorkspaceSizeCapacity")->willReturnSelf();

        $this->fileFactory = new FileFactory(
            self::getContainer()->getParameter("workspace_path"),
            $this->validator,
            $filePreviewService,
            $filesystem,
            self::getContainer()->get(EntityManagerInterface::class),
            $fileSizeService
        );
    }

    public function getUploadedFile(string $name, string $type): UploadedFile
    {
        $path = __DIR__ . "/../fixtures/document.pdf"; // unused
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$path, $name])
            ->getMock();

        $uploadedFile->method("getClientOriginalName")->willReturn($name);
        $uploadedFile->method("getClientMimeType")->willReturn($type);
        $uploadedFile->method("getPathname")->willReturn($path);

        return $uploadedFile;
    }

    public function getFakeFolder(): Folder
    {
        $workspace = new Workspace();
        $folder = $this->createMock(Folder::class);
        $folder->method("getWorkspace")->willReturn($workspace);
        return $folder;
    }

    public function testCreateValidFileFromUpload(): void
    {
        $name = "document.pdf";
        $type = "application/pdf";
        $uploadedFile = $this->getUploadedFile($name, $type);
        $folder = $this->getFakeFolder();

        $file = $this->fileFactory->createFileFromUpload($uploadedFile, $folder);

        $this->assertInstanceOf(File::class, $file);

        $this->assertEquals($name, $file->getName());
        $this->assertEquals($type, $file->getMime());

        $this->assertEquals($folder, $file->getFolder());
        $this->assertEquals($folder->getWorkspace(), $file->getWorkspace());
    }

    public function testCreateInvalidFile(): void
    {
        $uploadedFile = $this->getUploadedFile("name", "type");
        $folder = $this->getFakeFolder();

        $violation = new ConstraintViolation('Validation error message', null, [], null, null, null);
        $violationList = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Validation error message');

        $this->fileFactory->createFileFromUpload($uploadedFile, $folder);
    }
}
