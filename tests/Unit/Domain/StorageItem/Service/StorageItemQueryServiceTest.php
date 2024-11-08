<?php

namespace App\Tests\Unit\Domain\StorageItem\Service;

use App\Domain\Member\Member;
use App\Domain\StorageItem\Service\StorageItemPermissionService;
use App\Domain\StorageItem\Service\StorageItemQueryService;
use App\Domain\StorageItem\StorageItemInterface;
use App\Domain\StorageItem\StorageItemRepository;
use App\Domain\User\User;
use App\Domain\Workspace\Workspace;
use App\Security\Permission;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class StorageItemQueryServiceTest extends TestCase
{
    private StorageItemQueryService $service;
    private StorageItemRepository $repository;
    private StorageItemPermissionService $permissionService;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(StorageItemRepository::class);
        $this->permissionService = $this->createMock(StorageItemPermissionService::class);
        $this->service = new StorageItemQueryService($this->repository, $this->permissionService);
    }

    public function testHandleRequest(): void
    {
        $request = new Request([
            'type' => 'File',
            'name' => 'test',
            'sort' => 'updated_at',
            'order' => 'DESC',
            'limit' => 10
        ]);

        $workspace = $this->createMock(Workspace::class);
        $workspace->method('getId')->willReturn(Uuid::v4());

        $user = $this->createMock(User::class);
        $member = $this->createMock(Member::class);

        $user->expects($this->once())
            ->method('getWorkspaceMember')
            ->with($workspace)
            ->willReturn($member);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(3))
            ->method('andWhere')
            ->willReturnSelf();
        $queryBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->willReturnSelf();

        $query = $this->createMock(AbstractQuery::class);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $item1 = $this->createMock(StorageItemInterface::class);
        $item2 = $this->createMock(StorageItemInterface::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([$item1, $item2]);

        $this->permissionService->expects($this->exactly(2))
            ->method('hasItemPermission')
            ->willReturnOnConsecutiveCalls(true, false);

        $result = $this->service->handleRequest($request, $workspace, $user);

        $this->assertCount(1, $result);
        $this->assertSame($item1, $result[0]);
    }

    public function testQueryItemsWithoutFilters(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $member = $this->createMock(Member::class);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->willReturnSelf();

        $query = $this->createMock(AbstractQuery::class);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $item = $this->createMock(StorageItemInterface::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([$item]);

        $this->permissionService->expects($this->once())
            ->method('hasItemPermission')
            ->with($member, Permission::READ, $item)
            ->willReturn(true);

        $result = $this->service->queryItems($workspace, $member);

        $this->assertCount(1, $result);
        $this->assertSame($item, $result[0]);
    }

    public function testQueryItemsWithAllFilters(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->method('getId')->willReturn(Uuid::v4());
        $member = $this->createMock(Member::class);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(3))
            ->method('andWhere')
            ->willReturnSelf();
        $queryBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->willReturnSelf();

        $query = $this->createMock(AbstractQuery::class);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $item1 = $this->createMock(StorageItemInterface::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([$item1]);

        $this->permissionService->expects($this->once())
            ->method('hasItemPermission')
            ->with($member, Permission::READ, $item1)
            ->willReturn(true);

        $result = $this->service->queryItems(
            $workspace,
            $member,
            'File',
            'test',
            'updated_at',
            'DESC',
            1
        );

        $this->assertCount(1, $result);
        $this->assertSame($item1, $result[0]);
    }
}
