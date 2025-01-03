<?php

namespace App\Controller;

use App\Repository\BanRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class BanController extends AbstractController
{

    public function __construct(
        private BanRepository $banRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('/bans/{page}', name: 'bans', priority: 2)]
    public function index(int $page = 1): Response
    {
        $tgdb = false;
        if ($this->isGranted('ROLE_BAN')) {
            //User has TGDB access, show all bans
            $tgdb = true;
            $pagination = $this->banRepository->getBans($page);
        } else {
            //User does not have TGDB access, limiting them to bans applied to
            //their ckey
            $pagination = $this->banRepository->getBansForPlayer(
                $page,
                $this->getUser()
            );
        }
        return $this->render('ban/index.html.twig', [
            'tgdb' => $tgdb,
            'pagination' => $pagination
        ]);
    }

    #[Route('/bans/player/{ckey}/{page}', name: 'player.bans', priority: 1)]
    public function playerBans(string $ckey, int $page = 1): Response
    {
        $ckey = $this->userRepository->findByCkey($ckey);
        $this->denyAccessUnlessGranted('ROLE_BAN');
        $pagination = $this->banRepository->getBansForPlayer(
            $page,
            $ckey
        );
        return $this->render('ban/index.html.twig', [
            'tgdb' => true,
            'pagination' => $pagination,
            'ckey' => $ckey,
            'breadcrumb' => [
                $ckey->getCkey() => $this->generateUrl('player', ['ckey' => $ckey->getCkey()]),
                'Bans' => $this->generateUrl('player.bans', ['ckey' => $ckey->getCkey()])
            ]
        ]);
    }

    #[Route('/ban/{id}', name: 'ban.view')]
    public function ban(int $id): Response
    {
        $tgdb = false;
        if ($this->isGranted('ROLE_BAN')) {
            $tgdb = true;
        }
        $ban = $this->banRepository->getBan($id);
        $this->denyAccessUnlessGranted('BAN_VIEW', $ban);
        return $this->render('ban/ban.html.twig', [
            'ban' => $ban,
            'tgdb' => $tgdb
        ]);
    }
}
