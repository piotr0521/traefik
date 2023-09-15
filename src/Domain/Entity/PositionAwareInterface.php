<?php

declare(strict_types=1);

namespace Groshy\Domain\Entity;

use Groshy\Entity\Position;

interface PositionAwareInterface
{
    public function getPosition(): ?Position;
}
