<?php

namespace App\Tests\Unit\Utils;

use App\Utils\DocumentConverter;
use PHPUnit\Framework\TestCase;

class DocumentConverterTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/document_converter_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            array_map('unlink', glob("$this->tempDir/*.*"));
            rmdir($this->tempDir);
        }
    }

    public function testIsMimeTypeValid()
    {
        $validMimeType = "application/vnd.oasis.opendocument.text";
        $invalidMimeType = "image/png";

        $this->assertTrue(DocumentConverter::isMimeTypeValid($validMimeType));
        $this->assertFalse(DocumentConverter::isMimeTypeValid($invalidMimeType));
    }

    public function testConvertDocxToPdf()
    {
        $filePath = __DIR__ . '/../../fixtures/document.docx';
        $outputPath = $this->tempDir . '/output.pdf';

        $success = DocumentConverter::convertToPdf($filePath, $outputPath);

        $this->assertTrue($success);
        $this->assertFileExists($outputPath);
        $this->assertStringStartsWith('%PDF', file_get_contents($outputPath));
    }
}
