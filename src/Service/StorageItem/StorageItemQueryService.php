<?php

namespace App\Service\StorageItem;

use App\Entity\Member;
use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\StorageItemRepository;
use App\Security\Permission;
use App\Service\PermissionService;
use App\Utils\RequestHandler;
use Symfony\Component\HttpFoundation\Request;

class StorageItemQueryService
{
    private const SORT_OPTIONS = ["updated_at", "created_at"];
    private const ORDER_OPTIONS = ["ASC", "DESC"];
    private const TYPE_OPTIONS = ["File", "Folder"];

    public function __construct(
        private readonly StorageItemRepository $storageItemRepository,
        private readonly PermissionService $permissionService
    ) {
    }

    public function handleRequest(Request $request, Workspace $workspace, User $user): array
    {
        return $this->queryItems(
            $workspace,
            $user->getWorkspaceMember($workspace),
            RequestHandler::getRequestParameter($request, "type"),
            RequestHandler::getRequestParameter($request, "name"),
            RequestHandler::getRequestParameter($request, "sort"),
            RequestHandler::getRequestParameter($request, "order"),
            RequestHandler::getRequestParameter($request, "limit")
        );
    }

    public function queryItems(
        Workspace $workspace,
        Member $member,
        ?string $type = null,
        ?string $name = null,
        ?string $sort = null,
        ?string $order = null,
        ?int $limit = null
    ): array {
        $qb = $this->storageItemRepository->createQueryBuilder('i');
        $qb->andWhere("i.workspace = :workspace")->setParameter('workspace', $workspace->getId()->toBinary());

        if ($type && in_array($type, self::TYPE_OPTIONS, true)) {
            $qb->andWhere('i INSTANCE OF :type')
                ->setParameter('type', $type);
        }

        if ($name) {
            $qb->andWhere('i.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($sort && in_array($sort, self::SORT_OPTIONS, true)) {
            if (!$order || in_array($order, self::ORDER_OPTIONS, true)) {
                $qb->orderBy("i." . $sort, $order ?? "DESC");
            }
        }

        $items = [];
        foreach ($qb->getQuery()->getResult() as $item) {
            if ($this->permissionService->hasItemPermission($member, Permission::READ, $item)) {
                $items[] = $item;
            }
            if ($limit && sizeof($items) >= $limit) {
                break;
            }
        }

        return $items;
    }
}
