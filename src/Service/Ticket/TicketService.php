<?php

namespace App\Service\Ticket;

use App\Repository\TicketRepository;

class TicketService
{
    public function __construct(
        private TicketRepository $ticketRepository
    ) {}

    public function getAllTickets(): array
    {
        $data = [];
        $tickets = $this->ticketRepository->exportTickets();
        foreach ($tickets as $t) {
            $bwoink = false;
            if (
                $t['sender'] &&
                    $t['recipient'] &&
                    $t['action'] === 'Ticket Opened'
            ) {
                $bwoink = true;
            }
            $data[$t['round_id']][$t['ticket']][] = [
                'message' => $t['message'],
                'recipient' => $t['recipient'],
                'sender' => $t['sender'],
                'action' => $t['action'],
                'bwoink' => $bwoink
            ];
        }
        foreach ($data as $ticket) {
            foreach ($ticket as &$t) {
                if ($t[0]['bwoink']) {
                    $admin = $t[0]['sender'];
                    $player = $t[0]['recipient'];
                } else {
                    $player = $t[0]['sender'];
                }
                foreach ($t as &$m) {
                    if ($m['sender'] === $player) {
                        $m['sender'] = 'player';
                    }
                    if ($m['sender'] === $admin) {
                        $m['sender'] = 'admin';
                    }
                    if ($m['recipient'] === $player) {
                        $m['recipient'] = 'player';
                    }
                }
            }
        }
        return $data;
    }
}
