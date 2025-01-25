<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class MessageController extends AbstractController
{

    public function __construct(
        private MessageRepository $messageRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('/messages/{page}', name: 'messages', priority: 2)]
    public function index(int $page = 1): Response
    {
        $tgdb = false;
        if ($this->isGranted('ROLE_BAN')) {
            //User has TGDB access, show all notes & messages
            $tgdb = true;
            $pagination = $this->messageRepository->getMessages($page);
        } else {
            //User has no access, just show their notes & messages
            $pagination = $this->messageRepository->getMessagesForPlayer(
                $page,
                $this->getUser(),
                true
            );
        }
        return $this->render('message/index.html.twig', [
            'pagination' => $pagination,
            'tgdb' => $tgdb
        ]);
    }

    #[IsGranted('ROLE_BAN')]
    #[Route('/messages/player/{ckey}/{page}', name: 'player.messages', priority: 1)]
    public function playerMessages(string $ckey, int $page = 1): Response
    {
        $ckey = $this->userRepository->findByCkey($ckey);
        $tgdb = true;
        $pagination = $this->messageRepository->getMessagesForPlayer(
            $page,
            $ckey,
            true
        );
        return $this->render('message/index.html.twig', [
            'pagination' => $pagination,
            'tgdb' => $tgdb,
            'ckey' => $ckey
        ]);
    }

    #[IsGranted('ROLE_BAN')]
    #[Route('/messages/round/{round}/{page}', name: 'round.messages', priority: 1)]
    public function roundMessages(int $round, int $page = 1): Response
    {
        $tgdb = true;
        $pagination = $this->messageRepository->getMessagesForRound(
            $page,
            $round,
            true
        );
        return $this->render('message/index.html.twig', [
            'pagination' => $pagination,
            'tgdb' => $tgdb,
            'round' => $round
        ]);
    }

    #[Route('/message/{id}', name: 'app.message')]
    public function single(int $id): Response
    {
        $tgdb = false;
        if ($this->isGranted('ROLE_BAN')) {
            $tgdb = true;
        }
        $message = $this->messageRepository->getMessage($id);
        $this->denyAccessUnlessGranted('MESSAGE_VIEW', $message);
        return $this->render('message/view.html.twig', [
            'tgdb' => $tgdb,
            'message' => $message,
            'breadcrumb' => [
                'Messages' => $this->generateUrl('messages'),
                "#" . $message->getId() => $this->generateUrl('app.message', [
                    'id' => $message->getId()
                ])
            ]
        ]);
    }
}
