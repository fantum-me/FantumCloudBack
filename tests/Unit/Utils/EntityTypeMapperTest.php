<?php

namespace App\Tests\Unit\Utils;

use App\Utils\EntityTypeMapper;
use PHPUnit\Framework\TestCase;

class EntityTypeMapperTest extends TestCase
{
    public function testGetNameFromClass()
    {
        $className = 'App\\Entity\\User';
        $result = EntityTypeMapper::getNameFromClass($className);

        $this->assertEquals('User', $result);
    }

    public function testGetNameFromClassWithoutNamespace()
    {
        $className = 'User';
        $result = EntityTypeMapper::getNameFromClass($className);

        $this->assertEquals('User', $result);
    }
}
