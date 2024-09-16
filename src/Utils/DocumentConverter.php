<?php

namespace App\Utils;

class DocumentConverter
{
    // https://wiki.openoffice.org/wiki/Documentation/OOo3_User_Guides/Getting_Started/File_formats
    const VALID_MIME_TYPES = [
        "application/vnd.oasis.opendocument.*",
        "application/vnd.openxmlformats-officedocument.*",

        "application/msword",
        "application/vnd.ms-excel",
        "application/vnd.ms-powerpoint",

        "*xml",
        "application/rtf",
        "text/plain",
        "text/csv",
        "text/html"
    ];

    public static function isMimeTypeValid(string $mimeType): bool
    {
        foreach (self::VALID_MIME_TYPES as $type) {
            if (fnmatch($type, $mimeType)) {
                return true;
            }
        }
        return false;
    }

    public static function convertToPdf(string $filePath, string $outputPath): bool
    {
        $outputDir = dirname($outputPath);
        $tempOutputDir = $outputDir . "/" . uniqid();
        $tempOutputPath = "$tempOutputDir/" . pathinfo($filePath, PATHINFO_FILENAME) . ".pdf";

        $command = "soffice --headless --convert-to pdf --outdir $tempOutputDir $filePath";
        $success = shell_exec($command);

        if ($success) {
            rename($tempOutputPath, $outputPath);
            rmdir($tempOutputDir);
        }

        return (bool)$success;
    }
}
