<?php

namespace App\Command;

use App\Service\Map\MapRendererService;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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
        private MapRendererService $mapRendererService
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
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('/tg/_maps/map_files')
        );
        foreach ($files as $file) {
            if ('dmm' === $file->getExtension()) {
                $io->info('Rendering ' . $file->getRealpath());
                $this->mapRendererService->getFromMapFile($file->getRealpath());
                $io->success('Done!');
            }
        }
        $io->success('All minimaps rendered!');

        return Command::SUCCESS;
    }
}
