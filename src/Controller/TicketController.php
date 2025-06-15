<?php
namespace App\Controller;

use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Service\ServerInformationService;
use App\Service\Ticket\PublicTicketService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TicketController extends AbstractController
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private ServerInformationService $serverInformationService,
        private UserRepository $userRepository
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route("/tickets/{page}", name: 'tickets', priority: 2)]
    public function index(int $page = 1): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $tgdb    = true;
            $tickets = $this->ticketRepository->getTickets($page);
        } else {
            $tgdb    = false;
            $tickets = $this->ticketRepository->getTicketsByCkey(
                $this->getUser()->getCkey(),
                $page
            );
        }
        return $this->render('ticket/index.html.twig', [
            'pagination' => $tickets,
            'tgdb'       => $tgdb,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route("/tickets/server/{server}/{page}", name: 'server.tickets', priority: 2)]
    public function getTicketsForServer(string $server, int $page = 1): Response
    {
        $server  = $this->serverInformationService->getServerByIdentifier($server);
        $tickets = $this->ticketRepository->getTicketsBy(
            't.server_port',
            $server->getPort(),
            $page
        );
        return $this->render('ticket/index.html.twig', [
            'pagination' => $tickets,
            'tgdb'       => true,
            'server'     => $server,
            'breadcrumb' => [
                'Tickets'                => $this->generateUrl('tickets'),
                $server->getIdentifier() => $this->generateUrl('server.tickets', [
                    'server' => $server->getIdentifier(),
                ]),
            ],
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
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
            'tgdb'       => true,
            'round'      => $round,
            'breadcrumb' => [
                'Tickets' => $this->generateUrl('tickets'),
                $round    => $this->generateUrl('round.tickets', [
                    'round' => $round,
                ]),
            ],
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route("/tickets/player/{ckey}/{page}", name: 'player.tickets', priority: 2)]
    public function getTicketsForCkey(string $ckey, int $page = 1): Response
    {
        $ckey    = $this->userRepository->findByCkey($ckey);
        $tickets = $this->ticketRepository->getTicketsByCkey($ckey->getCkey(), $page);
        return $this->render('ticket/index.html.twig', [
            'pagination' => $tickets,
            'tgdb'       => true,
            'ckey'       => $ckey,
            'breadcrumb' => [
                $ckey->getCkey() => $this->generateUrl('player', ['ckey' => $ckey->getCkey()]),
                'Tickets'        => $this->generateUrl('player.tickets', [
                    'ckey' => $ckey->getCkey(),
                ]),
            ],
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route("/tickets/{round}/{ticket}", name: 'ticket', priority: 1)]
    public function getTicket(int $round, int $ticket, PublicTicketService $publicTicketService): Response
    {
        $ticket       = $this->ticketRepository->getTicket($round, $ticket);
        $participants = [];
        foreach ($ticket as $t) {
            $participants[] = $t->getSender();
            $participants[] = $t->getRecipient();
        }
        if (! $this->isGranted('ROLE_ADMIN')) {
            foreach ($ticket as &$t) {
                $t->censor();
            }
        }
        $this->denyAccessUnlessGranted('TICKET_VIEW', $ticket);
        $canBePublic = PublicTicketService::canBePublic(
            $ticket,
            $this->getUser()
        );
        if ($canBePublic) {
            $ticket[0]->setCanBePublic($canBePublic);
            $ticket[0]->setIdentifier(
                $publicTicketService->getTicketIdentifier(
                    $ticket,
                    $this->getUser()
                )
            );
        }
        return $this->render('ticket/view.html.twig', [
            'ticket'       => $ticket,
            'participants' => array_filter(array_unique($participants)),
            'breadcrumb'   => [
                'Tickets'                     => $this->generateUrl('tickets'),
                $ticket[0]->getRound()        => $this->generateUrl('round.tickets', [
                    'round' => (int) $ticket[0]->getRound(),
                ]),
                "#" . $ticket[0]->getNumber() => $this->generateUrl('ticket', [
                    'round'  => $ticket[0]->getRound(),
                    'ticket' => $ticket[0]->getNumber(),
                ]),
            ],
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        "/tickets/{round}/{ticket}/public",
        name: 'ticket.publicity',
        priority: 1,
        methods: ["POST"]
    )]
    public function toggleTicketPublicity(
        int $round,
        int $ticket,
        PublicTicketService $publicTicketService
    ): Response {
        $ticket = $this->ticketRepository->getTicket($round, $ticket);
        $this->denyAccessUnlessGranted('TICKET_VIEW', $ticket);
        $canBePublic = PublicTicketService::canBePublic(
            $ticket,
            $this->getUser()
        );
        if ($canBePublic) {
            $ticket[0]->setCanBePublic($canBePublic);
            $ticket[0]->setIdentifier(
                $publicTicketService->getTicketIdentifier(
                    $ticket,
                    $this->getUser()
                )
            );
            if (in_array($round, $this->serverInformationService->getCurrentRounds())) {
                throw new Exception("Can't publicize a ticket from an ongoing round");
            }
            $publicTicketService->toggleTicket($ticket, $this->getUser());
        }
        return $this->redirectToRoute('ticket', [
            'round'  => $ticket[0]->getRound(),
            'ticket' => $ticket[0]->getNumber(),
        ]);
    }

    #[Route("/tickets/public/{identifier}", name: "ticket.public", priority: 2)]
    public function publicTicket(
        string $identifier,
        PublicTicketService $publicTicketService
    ): Response {

        if (! $ticket = $publicTicketService->getTicketFromIdentifier($identifier)) {
            throw new Exception("This does not exist");
        }
        $ticket = $this->ticketRepository->getTicket(
            (int) $ticket['round'],
            (int) $ticket['ticket']
        );
        $ticket[0]->setIdentifier($identifier);
        foreach ($ticket as &$t) {
            $t->censor();
        }

        $participants = [];
        foreach ($ticket as $t) {
            $participants[] = $t->getSender();
            $participants[] = $t->getRecipient();
        }

        return $this->render('ticket/view.html.twig', [
            'ticket'       => $ticket,
            'participants' => array_filter(array_unique($participants)),
        ]);
    }
}
