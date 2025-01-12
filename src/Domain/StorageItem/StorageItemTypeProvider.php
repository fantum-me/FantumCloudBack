<?php

namespace App\Domain\StorageItem;

use App\Domain\DataTable\DataTableFactory;
use App\Domain\DataTable\Entity\DataTable;
use App\Domain\File\File;
use App\Domain\File\FileFactory;
use App\Domain\Folder\Folder;
use App\Domain\Folder\FolderFactory;

/**
 * This class provides a bridge between the StorageItem type and a specific StorageItem type.
 */
readonly class StorageItemTypeProvider
{
    /**
     * @var StorageItemFactoryInterface[] $factories
     */
    private array $factories;

    public function __construct(
        private FileFactory $fileFactory,
        private FolderFactory    $folderFactory,
        private DataTableFactory $dataTableFactory,
    )
    {
        $this->factories = [
            $this->fileFactory,
            $this->folderFactory,
            $this->dataTableFactory,
        ];
    }

    const TYPES = [
        "file" => File::class,
        "folder" => Folder::class,
        "database" => DataTable::class,
    ];

    const TYPES_IN_FILESYSTEM = [
        self::TYPES["file"],
        self::TYPES["folder"]
    ];


    public function getFactory(string $type): ?StorageItemFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if (in_array($type, $factory->getSupportedTypes())) {
                return $factory;
            }
        }
        return null;
    }

    public static function isInFilesystem(StorageItemInterface $storageItem): bool
    {
        foreach (self::TYPES_IN_FILESYSTEM as $type) {
            if ($storageItem instanceof $type) return true;
        }
        return false;
    }
}
