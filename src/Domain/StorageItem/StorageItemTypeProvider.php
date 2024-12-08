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
        private FileFactory               $fileFactory,
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

    const DISCRIMINATOR_MAP = [
        "file" => File::class,
        "folder" => Folder::class,
        "datatable" => DataTable::class,
    ];

    const TYPES = [
        "file" => File::class,
        "folder" => Folder::class,
        "database" => DataTable::class,
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
}
