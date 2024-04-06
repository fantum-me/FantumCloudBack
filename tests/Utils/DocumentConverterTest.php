<?php

namespace App\Tests\Utils;

use Generator;
use PHPUnit\Framework\TestCase;
use App\Utils\DocumentConverter;
use Symfony\Component\Filesystem\Filesystem;

class DocumentConverterTest extends TestCase
{
    public function mimeTypeDataProvider(): Generator
    {
        foreach (DocumentConverter::VALID_MIME_TYPES as $MIME_TYPE) {
            yield [$MIME_TYPE, true];
        }
        yield ["audio/aac", false];
        yield ["video/mp4", false];
        yield ["application/zip", false];
    }

    /**
     * @dataProvider mimeTypeDataProvider
     */
    public function testMimeTypeValidator(string $type, bool $expected): void
    {
        $isValid = DocumentConverter::isMimeTypeValid($type);
        $this->assertEquals($expected, $isValid);
    }

    public function testConvertToPdf(): void
    {
        $filesystem = new Filesystem();

        $outputPath = sys_get_temp_dir() . "/" . uniqid() . ".pdf";
        $done = DocumentConverter::convertToPdf(__DIR__ . "/../fixtures/document.docx", $outputPath);

        $this->assertTrue($done);
        $this->assertFileExists($outputPath);

        $filesystem->remove($outputPath);
    }
}
