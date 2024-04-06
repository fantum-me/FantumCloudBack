<?php

namespace App\Entity\Interface;

use Symfony\Component\Uid\Uuid;

interface UidInterface
{
    public function getId(): Uuid;
}
