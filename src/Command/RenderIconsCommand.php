<?php

namespace App\Command;

use App\Service\Icons\RenderDMI;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:render-icons',
    description: 'Add a short description for your command'
)]
class RenderIconsCommand extends Command
{
    public function __construct(
        private RenderDMI $renderDMI
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'arg1',
            InputArgument::OPTIONAL,
            'Argument description'
        )->addOption(
            'option1',
            null,
            InputOption::VALUE_NONE,
            'Option description'
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $finder = (new Finder())
            ->files()
            ->in([$this->renderDMI->getMobIconDir()])
            ->name('*.dmi')
            ->append(
                (new Finder())
                    ->files()
                    ->in([$this->renderDMI->getIconDir() . '/obj'])
                    ->name('card.dmi')
            );
        if ($finder->hasResults()) {
            $i = 0;
            $s = 0;
            foreach ($finder as $file) {
                try {
                    $s += $this->renderDMI->render($file);
                    $i++;
                } catch (Exception $e) {
                    $output->writeln(
                        'Skipping ' .
                        $file->getRealPath() .
                            ': ' .
                            $e->getMessage()
                    );
                }
            }
        }
        $output->writeln("Parsed $i files and generated $s sprites");
        return Command::SUCCESS;
    }
}
