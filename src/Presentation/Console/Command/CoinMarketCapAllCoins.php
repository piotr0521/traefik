<?php

declare(strict_types=1);

namespace Groshy\Presentation\Console\Command;

use CoinMarketCap\Api;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'groshy:cmc:all')]
final class CoinMarketCapAllCoins extends Command
{
    public function __construct(private readonly Api $api)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command dump a full list of crypto currencies from https://coinmarketcap.com/')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = $this->api->cryptocurrency()->map()['data'];
        $fileName = __DIR__.'/../DataFixtures/files/crypto_all.yaml';
        file_put_contents($fileName, Yaml::dump($data));

        return Command::SUCCESS;
    }
}
