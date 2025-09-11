<?php

namespace App\Command;

use App\Service\Map\MapService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:render-minimaps',
    description: 'Renders minimaps of all .dmm files in /tg/_maps/map_files'
)]
class RenderMinimapsCommand extends Command
{
    public function __construct(
        private MapService $mapService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $this->mapService->buildMaplist();
        $this->mapService->parseMaps();

        // $io->success('All minimaps rendered!');

        return Command::SUCCESS;
    }
}
