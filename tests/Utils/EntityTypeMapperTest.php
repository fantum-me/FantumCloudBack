<?php

namespace App\Tests\Utils;

use App\Entity\File;
use App\Entity\Folder;
use App\Utils\EntityTypeMapper;
use PHPUnit\Framework\TestCase;

class EntityTypeMapperTest extends TestCase
{
    public function testGetNameFromClass()
    {
        $this->assertEquals("File", EntityTypeMapper::getNameFromClass(File::class));
        $this->assertEquals("Folder", EntityTypeMapper::getNameFromClass(Folder::class));
    }
}
