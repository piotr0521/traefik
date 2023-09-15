<?php

namespace Groshy\Config;

use Symfony\Component\Serializer\Annotation\Ignore;

class ConfigModel
{
    #[Ignore]
    public ?string $assetClass = null;

    #[Ignore]
    public ?string $positionClass = null;

    public ?string $positionUrl = null;

    public ?array $positionEventTypes = null;

    #[Ignore]
    public ?bool $allowTransactionImportExport = null;
}
