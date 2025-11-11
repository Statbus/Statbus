<?php

namespace App\Controller;

use App\Attribute\FeatureEnabled;
use App\Entity\MenuItem;
use App\Entity\Round;
use App\Entity\Server;
use App\Form\RoundRatingType;
use App\Repository\RoundRepository;
use App\Service\Death\DeathService;
use App\Service\Death\HeatmapService;
use App\Service\FeatureFlagService;
use App\Service\Map\MapService;
use App\Service\Player\ManifestService;
use App\Service\Round\RoundStatsService;
use App\Service\Round\RoundTimelineService;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RoundController extends AbstractController
{
    public function __construct(
        private RoundRepository $roundRepository,
        private RoundStatsService $roundStatService,
        private ManifestService $manifestService,
        private FeatureFlagService $feature
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
        if ($this->feature->isEnabled('manifest')) {
            $stats['manifest'] =
                $this->manifestService->getManifestForRound($round);
        }
        $timeline = RoundTimelineService::sortStatsIntoTimeline($stats);
        $playerInRound = null;
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
            'playerInRound' => $playerInRound,
            'links' => $this->generateRoundLinks($round, 'round')['round']
        ]);
    }

    #[Route('/round/{round}/popover', name: 'round.popover')]
    public function popover(int $round): Response
    {
        return $this->render('round/popover.html.twig', [
            'round' => $round
        ]);
    }

    #[FeatureEnabled('round.map')]
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
            'map' => $map,
            'links' => $this->generateRoundLinks($round, 'round.map')['round']
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
    public function stats(
        Request $request,
        int $round,
        ?string $stat = null
    ): Response {
        $id = $round;
        $round = $this->roundRepository->findOneBy('id', $round);
        if (!$round) {
            return $this->render('round/notfound.html.twig', [
                'round' => $id
            ]);
        }
        $clearCache = $request->query->get('clearCache', false);
        if ($clearCache && $this->isGranted('ROLE_ADMIN')) {
            $this->roundStatService->clearCachedStatsForRound($round);
            return $this->redirectToRoute('round.stats', ['round' =>
                $round->getId()]);
        }
        if ($stat) {
            $stat = $this->roundStatService->getStatForRound($round, $stat);
        }
        if ($this->feature->isEnabled('round.logs') && $round->logUrl) {
            $stats['Statbus Generated'] =
                $this->roundStatService::STATBUS_GENERATED;
        }
        $stats['Database Stats'] =
            $this->roundStatService->listStatsForRound($round);
        return $this->render('round/stats.html.twig', [
            'round' => $round,
            'stats' => $stats,
            'stat' => $stat,
            'links' => $this->generateRoundLinks($round, 'round.stats')['round']
        ]);
    }

    #[FeatureEnabled('round.logs')]
    #[Route('/round/{round}/logs', name: 'round.logs')]
    public function logs(int $round): Response
    {
        $round = $this->roundRepository->findOneBy('id', $round);
        if (!$round->logUrl) {
            throw new Exception('Logs for this round are not available');
        }
        return $this->redirect($round->logUrl);
    }

    #[FeatureEnabled('round.logs.raw')]
    #[Route('/round/{round}/raw', name: 'round.raw')]
    public function raw(int $round): Response
    {
        $round = $this->roundRepository->findOneBy('id', $round);
        if (!$round->rawLogUrl) {
            throw new Exception('Logs for this round are not available');
        }
        return $this->redirect($round->rawLogUrl);
    }

    private function generateRoundLinks(
        Round $round,
        ?string $active = null
    ): array {
        $links = [
            'round' => new MenuItem(
                title: 'Round',
                icon: 'fas fa-circle',
                url: $this->generateUrl('round', ['round' => $round->getId()])
            ),
            'round.stats' => new MenuItem(
                title: 'Stats',
                icon: 'fa-solid fa-magnifying-glass-chart',
                url: $this->generateUrl('round.stats', ['round' =>
                    $round->getId()])
            ),
            'round.map' => new MenuItem(
                title: 'Map',
                icon: 'fa-solid fa-map',
                url: $this->generateUrl('round.map', ['round' =>
                    $round->getId()])
            )
        ];
        if ($round->logUrl) {
            $links['round.logs'] = new MenuItem(
                title: 'Logs',
                icon: 'fa-solid fa-file-lines',
                url: $this->generateUrl('round.logs', ['round' =>
                    $round->getId()])
            );
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $links['tickets'] = new MenuItem(
                title: 'Tickets',
                icon: 'fa-solid fa-ticket',
                url: $this->generateUrl('round.tickets', ['round' =>
                    $round->getId()])
            );
            $links['bans'] = new MenuItem(
                title: 'Bans',
                icon: 'fa-solid fa-hammer',
                url: $this->generateUrl('round.bans', ['round' =>
                    $round->getId()])
            );
            $links['messages'] = new MenuItem(
                title: 'Notes & Messages',
                icon: 'fa-solid fa-envelope',
                url: $this->generateUrl('round.messages', ['round' =>
                    $round->getId()])
            );
        }
        $links['gentoo'] = new MenuItem(
            title: 'Install Gentoo',
            icon: '',
            url: 'https://www.gentoo.org/get-started/',
            btn: 'gentoo',
            img: '/img/gentoo-3d-small.png'
        );
        $links = $this->feature->handleMenuItems(['round' => $links]);
        foreach ($links['round'] as $k => &$l) {
            if ($k === $active) {
                $l->btn .= ' active';
            }
        }
        return $links;
    }
}
