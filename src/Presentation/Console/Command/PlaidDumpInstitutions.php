<?php

declare(strict_types=1);

namespace Groshy\Presentation\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TomorrowIdeas\Plaid\Plaid;

#[AsCommand(name: 'groshy:plaid:institutions')]
final class PlaidDumpInstitutions extends Command
{
    public function __construct(
        private readonly Plaid $plaid,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command downloads all institutions from Plaid and dumps them into the file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = [];
        $data = $this->plaid->institutions->list(1, 0, ['US'], ['include_optional_metadata' => true]);
        $count = $data->total;
        for ($i = 0; $i <= $count; $i = $i + 500) {
            $output->writeln('Offset: '.$i);
            $data = $this->plaid->institutions->list(500, $i, ['US'], ['include_optional_metadata' => true]);
            foreach ($data->institutions as $el) {
                $result[] = [
                    'plaidId' => $el->institution_id,
                    'name' => $el->name,
                    'url' => $el->url,
                ];
            }
            sleep(15);
        }
        $fileName = realpath(__DIR__.'/../DataFixtures/files').'/institutions.yaml';
        file_put_contents($fileName, Yaml::dump($result));

        return Command::SUCCESS;
    }
}
