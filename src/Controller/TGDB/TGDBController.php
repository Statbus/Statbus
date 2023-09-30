<?php

namespace App\Controller\TGDB;

use App\Controller\Controller;
use App\Domain\Note\Repository\NoteRepository;
use Psr\Http\Message\ResponseInterface;
use DI\Attribute\Inject;

class TGDBController extends Controller
{
    #[Inject]
    private NoteRepository $memos;

    public function action(): ResponseInterface
    {
        $apps = [
            [
                'name' => 'Notes & Messages',
                'icon' => 'fa-solid fa-envelope',
                'url' => $this->getUriForRoute('tgdb.notes'),
            ],
            [
                'name' => 'Recently Edited Notes',
                'icon' => 'fa-solid fa-pen',
                'url' => $this->getUriForRoute('tgdb.notes.edited'),
            ],
            [
                'name' => 'Bans',
                'icon' => 'fa-solid fa-gavel',
                'url' => $this->getUriForRoute('tgdb.bans'),
            ],
            [
                'name' => 'Tickets',
                'icon' => 'fa-solid fa-ticket',
                'url' => $this->getUriForRoute('tgdb.tickets'),
            ],
            [
                'name' => 'Live Tickets',
                'icon' => 'fa-solid fa-circle text-danger ping',
                'url' => $this->getUriForRoute('tgdb.tickets.live'),
            ],
            [
                'name' => 'Guide to TLP',
                'icon' => 'fa-solid fa-traffic-light',
                'url' => $this->getUriForRoute('tgdb.tlp'),
            ],
            [
                'name' => 'Watchlist Entries',
                'icon' => 'fa-solid fa-binoculars',
                'url' => $this->getUriForRoute('tgdb.watchlist'),
            ],
            [
                'name' => 'Your Feedback Link',
                'icon' => 'fa-solid fa-bullhorn',
                'url' => $this->getUriForRoute('tgdb.feedback'),
            ],
            [
                'name' => 'New Players',
                'icon' => 'fa-solid fa-users',
                'url' => $this->getUriForRoute('tgdb.newplayers'),
            ],
        ];
        $memos = $this->memos->getCurrentMemos();
        $notes = $this->memos->getNotes(1, 10)->getResults();
        return $this->render('tgdb/index.html.twig', [

            'apps' => $apps,
            'memos' => $memos,
            'notes' => $notes
        ]);
    }

}
