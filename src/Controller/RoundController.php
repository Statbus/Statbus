<?php

namespace App\Controller;

use App\Repository\RoundRepository;
use App\Service\Player\ManifestService;
use App\Service\Round\RoundStatsService;
use App\Service\Round\RoundTimelineService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoundController extends AbstractController
{
    public function __construct(
        private RoundRepository $roundRepository,
        private RoundStatsService $roundStatService,
        private ManifestService $manifestService
    ) {}

    #[Route('/rounds/{page}', name: 'rounds')]
    public function index(int $page = 1): Response
    {
        $rounds = $this->roundRepository->getRounds($page);
        return $this->render('round/index.html.twig', [
            'rounds' => $rounds,
            'pager' => $this->roundRepository->getPager()
        ]);
    }

    #[Route('/round/{round}', name: 'round')]
    public function round(int $round): Response
    {
        $id = $round;
        $round = $this->roundRepository->getRound($round);
        $stats = null;
        $timeline = null;
        if (!$round) {
            return $this->render('round/notfound.html.twig', [
                'round' => $id
            ]);
        }
        $stats = $this->roundStatService->getRoundStats($round, [
            'dynamic_threat',
            'nuclear_challenge_mode',
            'testmerged_prs',
            'explosion'
        ]);
        $stats['manifest'] =
            $this->manifestService->getManifestForRound($round);
        $timeline = RoundTimelineService::sortStatsIntoTimeline($stats);
        return $this->render('round/round.html.twig', [
            'round' => $round,
            'stats' => $stats,
            'timeline' => $timeline
        ]);
    }

    #[Route('/round/{round}/popover', name: 'round.popover')]
    public function popover(int $round): Response
    {
        return $this->render('round/popover.html.twig', [
            'round' => $round
        ]);
    }
}
