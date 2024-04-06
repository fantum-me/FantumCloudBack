<?php

namespace App\Security;

class Permission
{
    public const READ = "read";
    public const WRITE = "write";
    public const TRASH = "trash";
    public const DELETE = "delete";
    public const EDIT_PERMISSIONS = "edit_permissions";

    public const PERMISSIONS = [
        self::READ,
        self::WRITE,
        self::TRASH,
        self::DELETE,
        self::EDIT_PERMISSIONS,
    ];
}
