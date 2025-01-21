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
                $this->getUser(),
                true
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
                'Issued Bans' => $this->generateUrl('player.bans', ['ckey' => $ckey->getCkey()])
            ]
        ]);
    }

    #[Route('/bans/by/player/{ckey}/{page}', name: 'admin.bans', priority: 2)]
    public function adminBans(string $ckey, int $page = 1): Response
    {
        $ckey = $this->userRepository->findByCkey($ckey);
        $this->denyAccessUnlessGranted('ROLE_BAN');
        $pagination = $this->banRepository->getBansByPlayer(
            $page,
            $ckey
        );
        return $this->render('ban/index.html.twig', [
            'tgdb' => true,
            'pagination' => $pagination,
            'author' => $ckey,
            'breadcrumb' => [
                $ckey->getCkey() => $this->generateUrl('player', ['ckey' => $ckey->getCkey()]),
                'Bans' => $this->generateUrl('admin.bans', ['ckey' => $ckey->getCkey()])
            ]
        ]);
    }

    #[Route('/bans/round/{round}/{page}', name: 'round.bans', priority: 2)]
    public function roundBans(int $round, int $page = 1): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BAN');
        $pagination = $this->banRepository->getBansForRound(
            $page,
            $round
        );
        return $this->render('ban/index.html.twig', [
            'tgdb' => true,
            'pagination' => $pagination,
            'round' => $round,
            'breadcrumb' => [
                $round => $this->generateUrl('round', ['round' => $round]),
                'Bans' => $this->generateUrl('round.bans', ['round' => $round])
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
        if (!$this->isGranted('ROLE_BAN')) {
            $ban->censor();
        }
        return $this->render('ban/ban.html.twig', [
            'ban' => $ban,
            'tgdb' => $tgdb
        ]);
    }
}
