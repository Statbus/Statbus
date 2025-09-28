<?php

namespace App\Controller;

use App\Entity\Round;
use App\Entity\Server;
use App\Form\RoundRatingType;
use App\Repository\RoundRepository;
use App\Service\Death\DeathService;
use App\Service\Death\HeatmapService;
use App\Service\Map\MapService;
use App\Service\Player\ManifestService;
use App\Service\Round\RoundStatsService;
use App\Service\Round\RoundTimelineService;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
            'pager' => $rounds
        ]);
    }

    #[Route('/round/{round}', name: 'round')]
    public function round(int $round): Response
    {
        $id = $round;
        $round = $this->roundRepository->findOneBy('id', $round);
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
        $stats['round'] = $round;
        $stats['manifest'] =
            $this->manifestService->getManifestForRound($round);
        $timeline = RoundTimelineService::sortStatsIntoTimeline($stats);
        if ($this->getUser()) {
            $playerInRound = $this->roundRepository->wasCkeyInRound(
                $this->getUser()->getCkey(),
                $round->getId()
            );
        }
        // $form = $this->createForm(RoundRatingType::class);
        return $this->render('round/round.html.twig', [
            'round' => $round,
            'stats' => $stats,
            'timeline' => $timeline,
            'playerInRound' => $playerInRound
            // 'form' => $form->createView()
        ]);
    }

    #[Route('/round/{round}/popover', name: 'round.popover')]
    public function popover(int $round): Response
    {
        return $this->render('round/popover.html.twig', [
            'round' => $round
        ]);
    }

    #[Route('/round/{round}/map', name: 'round.map')]
    public function map(
        int $round,
        MapService $mapService,
        SluggerInterface $slugger
    ): Response {
        $id = $round;
        $round = $this->roundRepository->findOneBy('id', $round);
        if ($round) {
            $map = $mapService->getMap(
                (string) $slugger->slug($round->getMap())->lower()
            );

            $map['dmmPath'] = pathinfo($map['dmmPath']);
        } else {
            $round = new Round(
                id: $id,
                init: new DateTimeImmutable(),
                server: new Server('fake', 0, 0, null, null, '127.0.0.1')
            );
            $map = null;
        }
        return $this->render('round/map.html.twig', [
            'round' => $round,
            'map' => $map
        ]);
    }

    #[Route('/api/{version}/round/{round}/{key}', name: 'round.api')]
    public function mapApi(
        int $round,
        string $key,
        string $version = 'v1',
        DeathService $deathService
    ): Response {
        $round = $this->roundRepository->findOneBy('id', $round);
        switch ($key) {
            case 'death':
                $data = $deathService->getDeathsForRound($round);
                break;
            case 'explosion':
                try {
                    $data = $this->roundStatService->getRoundStats($round, [
                        'explosion'
                    ])['explosion']->getData();
                } catch (Exception $e) {
                    dump($e);
                }
                break;
        }
        return $this->json($data ?? []);
    }

    #[Route('/round/{round}/stats/{stat}', name: 'round.stats')]
    public function stats(int $round, ?string $stat = null): Response
    {
        $round = $this->roundRepository->findOneBy('id', $round);
        if ($stat) {
            $stat = $this->roundStatService->getStatForRound($round, $stat);
        }
        return $this->render('round/stats.html.twig', [
            'round' => $round,
            'stats' => $this->roundStatService->listStatsForRound($round),
            'stat' => $stat
        ]);
    }

    #[Route('/round/{round}/logs', name: 'round.logs')]
    public function logs(int $round): Response
    {
        $round = $this->roundRepository->findOneBy('id', $round);
        if (!$round->logUrl) {
            throw new Exception('Logs for this round are not available');
        }
        return $this->redirect($round->logUrl);
    }
}
