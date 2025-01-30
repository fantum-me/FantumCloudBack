<?php

namespace App\Domain\StorageItem;

use App\Domain\Folder\Folder;
use Symfony\Component\HttpFoundation\Request;

interface StorageItemFactoryInterface
{
    public function handleInsertRequest(Request $request, string $name, Folder $parent): StorageItem;
    public function getSupportedTypes(): array;
}
