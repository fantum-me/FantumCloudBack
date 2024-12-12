<?php

namespace App\Domain\DataTable;

use App\Domain\DataTable\Entity\DataTable;
use App\Domain\DataTable\Entity\DataView;
use App\Domain\DataTable\Entity\TableField;
use App\Domain\Folder\Folder;
use App\Domain\StorageItem\StorageItem;
use App\Domain\StorageItem\StorageItemFactoryInterface;
use App\Utils\RequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataTableFactory implements StorageItemFactoryInterface
{
    public function __construct(
        private readonly ValidatorInterface     $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    )
    {
    }

    public function handleInsertRequest(Request $request, string $name, Folder $parent): StorageItem
    {
        $view = RequestHandler::getRequestParameter($request, "view", true);
        $viewType = DataTableViewType::from($view);
        return $this->createDataTable($name, $viewType, $parent);
    }

    public function createDataTable(string $name, DataTableViewType $viewType, Folder $parent): DataTable
    {
        $user = $this->security->getUser();
        $member = $user->getWorkspaceMember($parent->getWorkspace());

        $field = new TableField();
        $field->setName("Name")
            ->setType(TableFieldType::TextType)
            ->setIsTitle(true);

        $datatable = new DataTable();
        $datatable->setName($name)
            ->addField($field)
            ->setWorkspace($parent->getWorkspace())
            ->setFolder($parent);

        $view = new DataView();
        $view->setName(ucfirst($viewType->value))
            ->setType($viewType)
            ->setCreatedBy($member);
        $datatable->addView($view);

        if (count($errors = $this->validator->validate($datatable)) > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $this->entityManager->persist($field);
        $this->entityManager->persist($datatable);
        $this->entityManager->persist($view);

        return $datatable;
    }

    public function getSupportedTypes(): array
    {
        return [DataTable::class];
    }
}
