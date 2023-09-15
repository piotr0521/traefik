<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCrypto;

use Groshy\Entity\Tag;

class UpdatePositionCryptoDto
{
    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];
}
