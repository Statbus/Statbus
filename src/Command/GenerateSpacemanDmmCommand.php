<?php

namespace App\Command;

use App\Service\Map\MapService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-spacemandmm-command',
    description: 'Add a short description for your command'
)]
class GenerateSpacemanDmmCommand extends Command
{
    private const BASE_COMMAND = <<<END
    docker run -v ~/Developer/TG/tgstation/:/tg \
        -v ~/Developer/TG/map_depot:/map_depot \
        -v ~/Developer/Statbus/renderbus.statbus.space/output:/output spacemandmm \
        --env /tg/tgstation.dme \
        minimap \
        -o /output \
        --disable icon-smoothing \
    END;

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
        $maps = $this->mapService->buildMaplist2();
        $io->success('SpacemanDMM Render Command:');
        $output->write(static::BASE_COMMAND, true);
        foreach ($maps as $k => $m) {
            $output->write("\t" . escapeshellarg($m->dmmPath));
            if ($k !== array_key_last($maps)) {
                $output->write("\\", true);
            }
        }
        $output->write('', true);
        $io->success('JSON Output (for renderbus):');
        $output->write(json_encode($maps), true);
        return Command::SUCCESS;
    }
}
