<?php

declare(strict_types=1);

namespace Groshy\Application\Query;

use DateTime;

final class UserGraphQuery
{
    public function __construct(
        public readonly DateTime $from,
        public readonly DateTime $to,
        public readonly array $positions,
    ) {
    }
}
