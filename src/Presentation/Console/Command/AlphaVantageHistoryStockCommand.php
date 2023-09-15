<?php

declare(strict_types=1);

namespace Groshy\Presentation\Console\Command;

use AlphaVantage\Api\TimeSeries;
use AlphaVantage\Client as AlphaVantageClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'groshy:av:stock:history')]
final class AlphaVantageHistoryStockCommand extends Command
{
    public function __construct(private readonly AlphaVantageClient $client)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows to dump history for one public security symbol')
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock/ETF ticker')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symbol = $input->getArgument('symbol');
        $fileName = realpath(__DIR__.'/../DataFixtures/files').'/'.strtolower($symbol).'.yaml';
        file_put_contents($fileName, Yaml::dump($this->client->timeSeries()->daily($symbol, TimeSeries::OUTPUT_TYPE_FULL)));

        return Command::SUCCESS;
    }
}
