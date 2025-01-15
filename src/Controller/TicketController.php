<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Service\ServerInformationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class TicketController extends AbstractController
{

    public function __construct(
        private TicketRepository $ticketRepository,
        private ServerInformationService $serverInformationService,
        private UserRepository $userRepository
    ) {}

    #[Route("/tickets/{page}", name: 'tickets', priority: 2)]
    public function index(int $page = 1): Response
    {
        if ($this->isGranted('ROLE_BAN')) {
            $tgdb = true;
            $tickets = $this->ticketRepository->getTickets($page);
        } else {
            $tgdb = false;
            $tickets = $this->ticketRepository->getTicketsByCkey(
                $this->getUser()->getCkey(),
                $page
            );
        }
        return $this->render('ticket/index.html.twig', [
            'pagination' => $tickets,
            'tgdb' => $tgdb,
        ]);
    }

    #[Route("/tickets/server/{server}/{page}", name: 'server.tickets', priority: 2)]
    public function getTicketsForServer(string $server, int $page = 1): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BAN');
        $server = $this->serverInformationService->getServerByIdentifier($server);
        $tickets = $this->ticketRepository->getTicketsBy(
            't.server_port',
            $server->getPort(),
            $page
        );
        return $this->render('ticket/index.html.twig', [
            'pagination' => $tickets,
            'tgdb' => true,
            'server' => $server,
            'breadcrumb' => [
                'Tickets' => $this->generateUrl('tickets'),
                $server->getIdentifier() => $this->generateUrl('server.tickets', [
                    'server' => $server->getIdentifier()
                ])
            ]
        ]);
    }

    #[Route("/tickets/round/{round}/{page}", name: 'round.tickets', priority: 2)]
    public function getTicketsForRound(int $round, int $page = 1): Response
    {
        $tickets = $this->ticketRepository->getTicketsBy(
            't.round_id',
            $round,
            $page
        );
        return $this->render('ticket/index.html.twig', [
            'pagination' => $tickets,
            'tgdb' => true,
            'round' => $round,
            'breadcrumb' => [
                'Tickets' => $this->generateUrl('tickets'),
                $round => $this->generateUrl('round.tickets', [
                    'round' => $round
                ])
            ]
        ]);
    }

    #[Route("/tickets/player/{ckey}/{page}", name: 'player.tickets', priority: 2)]
    public function getTicketsForCkey(string $ckey, int $page = 1): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BAN');
        $ckey = $this->userRepository->findByCkey($ckey);
        $tickets = $this->ticketRepository->getTicketsByCkey($ckey->getCkey(), $page);
        return $this->render('ticket/index.html.twig', [
            'pagination' => $tickets,
            'tgdb' => true,
            'ckey' => $ckey,
            'breadcrumb' => [
                $ckey->getCkey() => $this->generateUrl('player', ['ckey' => $ckey->getCkey()]),
                'Tickets' => $this->generateUrl('player.tickets', [
                    'ckey' => $ckey->getCkey()
                ]),
            ]
        ]);
    }

    #[Route("/tickets/{round}/{ticket}", name: 'ticket', priority: 1)]
    public function getTicket(int $round, int $ticket): Response
    {
        $ticket = $this->ticketRepository->getTicket($round, $ticket);
        $participants = [];
        foreach ($ticket as $t) {
            $participants[] = $t->getSender();
            $participants[] = $t->getRecipient();
        }
        if (!$this->isGranted('ROLE_BAN')) {
            foreach ($ticket as &$t) {
                $t->censor();
            }
        }
        $this->denyAccessUnlessGranted('TICKET_VIEW', $ticket);
        return $this->render('ticket/view.html.twig', [
            'ticket' => $ticket,
            'participants' => array_filter(array_unique($participants)),
            'breadcrumb' => [
                'Tickets' => $this->generateUrl('tickets'),
                $ticket[0]->getRound() => $this->generateUrl('round.tickets', [
                    'round' => (int) $ticket[0]->getRound()
                ]),
                "#" . $ticket[0]->getNumber() => $this->generateUrl('ticket', [
                    'round' => $ticket[0]->getRound(),
                    'ticket' => $ticket[0]->getNumber()
                ])
            ]
        ]);
    }
}
