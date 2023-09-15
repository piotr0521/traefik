<?php

declare(strict_types=1);

namespace Groshy\Entity;

use DateTime;

interface PositionStatsInterface
{
    public function getStatsData(DateTime $from, DateTime $to): PositionStatsData;
}
