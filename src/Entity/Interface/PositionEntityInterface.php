<?php

namespace App\Entity\Interface;

interface PositionEntityInterface
{
    public function getPosition(): ?int;

    public function setPosition(?int $position): static;
}
