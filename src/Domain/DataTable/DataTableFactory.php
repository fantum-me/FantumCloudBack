<?php

namespace App\Domain\DataTable;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\TableField;
use App\Domain\Folder\Folder;
use App\Domain\StorageItem\StorageItem;
use App\Domain\StorageItem\StorageItemFactoryInterface;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataTableFactory implements StorageItemFactoryInterface
{
    public function __construct(
        private readonly ValidatorInterface     $validator,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function handleInsertRequest(Request $request, string $name, Folder $parent): StorageItem
    {
        $view = RequestHandler::getRequestParameter($request, "view", true);
        $view = DataTableViewType::from($view);
        return $this->createDataTable($name, $view, $parent);
    }

    public function createDataTable(string $name, DataTableViewType $view, Folder $parent): DataTable
    {
        $field = new TableField();
        $field->setName("Name")
            ->setType(TableFieldType::TextType)
            ->setIsTitle(true);

        $datatable = new DataTable();
        $datatable->setName($name)
            ->setViews($view)
            ->addField($field)
            ->setWorkspace($parent->getWorkspace())
            ->setFolder($parent);

        if (count($errors = $this->validator->validate($datatable)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->entityManager->persist($field);
        $this->entityManager->persist($datatable);

        return $datatable;
    }

    public function getSupportedTypes(): array
    {
        return [DataTable::class];
    }
}
