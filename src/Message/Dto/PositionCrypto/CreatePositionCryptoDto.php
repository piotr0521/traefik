<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCrypto;

use DateTime;
use Groshy\Entity\AssetCrypto;
use Groshy\Entity\Tag;
use Money\Money;
use Symfony\Component\Serializer\Annotation\Ignore;
use Talav\Component\User\Model\UserInterface;

class CreatePositionCryptoDto
{
    public ?DateTime $purchaseDate = null;

    public ?float $quantity = null;

    public ?Money $averagePrice = null;

    public ?AssetCrypto $asset = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
