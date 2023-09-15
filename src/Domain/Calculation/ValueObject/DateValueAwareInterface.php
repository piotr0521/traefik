<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\ValueObject;

use DateTime;

interface DateValueAwareInterface
{
    public function getDate(): DateTime;

    public function getValue(): mixed;
}
