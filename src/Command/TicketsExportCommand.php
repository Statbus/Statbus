<?php

namespace App\Command;

use App\Service\Ticket\TicketService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tickets:export',
    description: 'Exports the ticket table to an LLM -friendly JSONL format'
)]
class TicketsExportCommand extends Command
{
    public function __construct(
        private TicketService $ticketService
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
        $io->success('Done!');
        $tickets = $this->ticketService->getAllTickets();
        dump($tickets[251351][8]);
        return Command::SUCCESS;
    }
}
