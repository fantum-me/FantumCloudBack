<?php

namespace App\Tests\Service\StorageItem;

use App\Entity\File;
use App\Service\StorageItem\FilePreviewService;
use DirectoryIterator;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class FilePreviewServiceTest extends KernelTestCase
{
    public function fileDataProvider(): Generator
    {
        foreach (new DirectoryIterator(__DIR__ . '/../../fixtures') as $fixture) {
            if ($fixture->isDot()) {
                continue;
            }

            $file = $this->createMock(File::class);

            $file->method("getPath")->willReturn($fixture->getFilename());

            $previewPath = $fixture->getFilename() . "-preview." . FilePreviewService::PREVIEW_FORMAT;
            $file->method("getPreviewPath")->willReturn($previewPath);

            $file->method("getMime")->willReturn(mime_content_type($fixture->getPathname()));

            yield [$file, $fixture->getPathname(), FilePreviewService::isTypePreviewable($file->getMime())];
        }
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testGeneratePreview(File $file, string $path, bool $expected): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $filePath = $container->getParameter("workspace_path") . "/" . $file->getPath();
        $filePreviewPath = $container->getParameter("workspace_path") . "/" . $file->getPreviewPath();

        $filesystem = $container->get(Filesystem::class);
        $filesystem->copy($path, $filePath);

        $this->assertFileExists($filePath);

        $filePreviewService = $container->get(FilePreviewService::class);
        $filePreviewService->generateFilePreview($file);
        $filesystem->remove($filePath);

        if ($expected) {
            $this->assertFileExists($filePreviewPath);
            $filesystem->remove($filePreviewPath);
        } else {
            $this->assertFileDoesNotExist($filePreviewPath);
        }
    }
}
