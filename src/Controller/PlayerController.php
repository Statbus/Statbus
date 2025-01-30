<?php

namespace App\Controller;

use App\Repository\AdminLogRepository;
use App\Repository\BanRepository;
use App\Repository\PlayerRepository;
use App\Service\Player\DiscordVerificationsService;
use App\Service\Player\IsBannedService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/player', name: 'player')]
class PlayerController extends AbstractController
{

    public function __construct(
        private Security $security,
        private PlayerRepository $playerRepository,
        private AdminLogRepository $adminLogRepository,
        private IsBannedService $isBannedService,
        private DiscordVerificationsService $discordVerificationsService
    ) {}

    #[Route('/{ckey}', name: '')]
    public function index(string $ckey): Response
    {
        $player = $this->playerRepository->findByCkey($ckey);
        $discord = null;
        $alts = null;
        if ($this->isGranted('ROLE_BAN')) {
            $player->setStanding($this->isBannedService->isPlayerBanned($player));
            $discord = $this->discordVerificationsService->findVerificationsForPlayer($player);
            $alts = $this->playerRepository->getKnownAlts($player);
        } else {
            $player->censor();
        }
        $adminLogs = $this->adminLogRepository->getAdminLogsForCkey($player);
        dump($alts);
        return $this->render('player/index.html.twig', [
            'player' => $player,
            'discord' => $discord,
            'adminlogs' => $adminLogs,
            'alts' => $alts
        ]);
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/{ckey}/report', name: '.report')]
    public function report(string $ckey): Response
    {
        $player = $this->playerRepository->findByCkey($ckey);
        if ($player->getCkey() === $this->getUser()->getCkey()) {
            throw new HttpException(400, "You cannot file a report against yourself.");
        }
        return $this->render('player/report.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route('/{ckey}/popover', name: '.popover')]
    public function popover(string $ckey): Response
    {
        $player = $this->playerRepository->findByCkey($ckey);
        if ($this->isGranted('ROLE_BAN')) {
            $player->setStanding($this->isBannedService->isPlayerBanned($player));
        }
        return $this->render('player/popover.html.twig', [
            'player' => $player
        ]);
    }

    #[Route('/{ckey}/playtime', name: '.playtime')]
    public function playtime(string $ckey): Response
    {
        return $this->json(
            $this->playerRepository->getPlayerRecentPlaytime($ckey)
        );
    }
}
