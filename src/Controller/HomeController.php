<?php

namespace App\Controller;

use App\Repository\TGRepository;
use App\Service\FeatureFlagService;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class HomeController extends AbstractController
{
    public function __construct(
        private FeatureFlagService $features,
        private readonly array $statbusFeatures
    ) {}

    #[Route('', name: 'app.home')]
    public function index(): Response
    {
        $links = [];
        if ($user = $this->getUser()) {
            $links['Statbus... Apps... Stapps?'] = $this->getUserMenu($user);

            if ($this->isGranted('ROLE_ADMIN')) {
                $links['Admin Tools'] = $this->getAdminMenu();
            }
        } else {
            $links['Authentication Options'] = $this->getAuthMenu();
        }
        $links['Tools'] = $this->getToolMenu();
        $links['Info'] = $this->getInfoMenu();
        $links['Statbus'] = $this->getStatbusMenu();
        foreach ($links as $category => &$l) {
            $l = array_filter(
                $l,
                fn($key) => $this->features->isEnabled($key),
                ARRAY_FILTER_USE_KEY
            );
        }
        return $this->render('home/index.html.twig', ['links' => $links]);
    }

    #[Route('/logout', name: 'app.logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('app.home');
    }

    #[Route('/privacy', name: 'privacy')]
    public function privacy(): Response
    {
        return $this->render('markdown.html.twig', [
            'title' => 'Privacy Policy',
            'content' => file_get_contents(dirname(__DIR__) . '/../privacy.md')
        ]);
    }

    #[Route('/changelog', name: 'changelog')]
    public function changelog(): Response
    {
        return $this->render('markdown.html.twig', [
            'title' => 'Changelog',
            'content' => file_get_contents(dirname(__DIR__) .
                '/../changelog.md')
        ]);
    }

    #[Route('/content-warning', name: 'content-warning')]
    public function contentWarning(): Response
    {
        return $this->render('markdown.html.twig', [
            'title' => 'Content Warning',
            'content' => file_get_contents(dirname(__DIR__) .
                '/../content-warning.md')
        ]);
    }

    #[Route('/debug')]
    public function debug(): Response
    {
        return $this->json($this->getUser()->getRoles());
    }

    #[Route('/ping')]
    public function ping(TGRepository $tgrepository): Response
    {
        $status = 'okay';
        $code = $tgrepository->pingDBServer();
        if ($code !== Response::HTTP_OK) {
            $status = 'There is an error with the TG Station database. Statbus is unavailable. Ned does not need to be alerted to this issue.';
        }
        return $this->json(['status' => $status], $code);
    }

    private function getUserMenu($user): array
    {
        return [
            new MenuItem(
                title: 'My Page',
                icon: 'fas fa-user',
                url: $this->generateUrl('player', ['ckey' => $user->getCkey()])
            ),
            new MenuItem(
                title: 'Bans',
                icon: 'fas fa-gavel',
                url: $this->generateUrl('bans')
            ),
            new MenuItem(
                title: 'Notes & Messages',
                icon: 'fas fa-envelope',
                url: $this->generateUrl('messages')
            ),
            'tickets' => new MenuItem(
                title: 'Tickets',
                icon: 'fas fa-ticket',
                url: $this->generateUrl('tickets')
            ),
            'library' => new MenuItem(
                title: 'Library',
                icon: 'fas fa-book',
                url: $this->generateUrl('library')
            )
        ];
    }

    private function getAuthMenu(): array
    {
        return [
            'auth.tgstation' => new MenuItem(
                title: 'TgStation Auth',
                icon: '',
                url: $this->generateUrl('auth.tgstation.start'),
                btn: 'tg-station text-white',
                img: floor(rand(0, 50)) ? 'img/tg.svg' : 'img/disaster.png'
            ),
            'auth.discord' => new MenuItem(
                title: 'Discord Auth',
                icon: 'fa-brands fa-discord',
                url: $this->generateUrl('auth.discord.start'),
                btn: 'discord text-white'
            )
        ];
    }

    private function getAdminMenu(): array
    {
        return [
            new MenuItem(
                title: 'Connections',
                icon: 'fa-solid fa-circle-nodes',
                url: $this->generateUrl('connections'),
                btn: 'btn-warning'
            ),
            new MenuItem(
                title: 'TelemetryDB',
                icon: 'fa-solid fa-satellite-dish',
                url: $this->generateUrl('telemetry'),
                btn: 'btn-warning'
            ),
            new MenuItem(
                title: 'New Players',
                icon: 'fa-solid fa-user-plus',
                url: $this->generateUrl('newplayers'),
                btn: 'btn-warning'
            )
        ];
    }

    private function getToolMenu(): array
    {
        $menuItems = [
            'badger' => new MenuItem(
                title: 'BadgeR',
                icon: 'fa-solid fa-id-badge',
                url: $this->generateUrl('badger')
            )
        ];
        return $menuItems;
    }

    private function getInfoMenu(): array
    {
        return [
            new MenuItem(
                title: 'Rounds',
                icon: 'fas fa-circle',
                url: $this->generateUrl('rounds')
            ),
            new MenuItem(
                title: 'Admin Roster',
                icon: 'fa-solid fa-user-shield',
                url: $this->generateUrl('app.admin_roster')
            ),
            new MenuItem(
                title: 'Admin Rank Logs',
                icon: 'fa-solid fa-users-line',
                url: $this->generateUrl('app.admin_log')
            ),
            'bans.public' => new MenuItem(
                title: 'Public Bans',
                icon: 'fas fa-file-lines',
                url: $this->generateUrl('bans.public')
            ),
            'polls' => new MenuItem(
                title: 'Polls',
                icon: 'fas fa-check-to-slot',
                url: $this->generateUrl('polls')
            ),
            'deaths.heatmaps' => new MenuItem(
                title: 'Death Heatmaps',
                icon: 'fa-solid fa-book-skull',
                url: $this->generateUrl('death.heatmap')
            ),
            'info.connections' => new MenuItem(
                title: 'Connection Stats',
                icon: 'fa-solid fa-circle-nodes',
                url: $this->generateUrl('info.connections') .
                    '?year=' .
                    (new DateTimeImmutable())->format('Y')
            )
        ];
    }

    private function getStatbusMenu(): array
    {
        return [
            new MenuItem(
                title: 'Privacy Policy',
                icon: 'fa-solid fa-lock',
                url: $this->generateUrl('privacy')
            ),
            new MenuItem(
                title: 'Content Warning',
                icon: 'fas fa-triangle-exclamation',
                url: $this->generateUrl('content-warning')
            ),
            new MenuItem(
                title: 'Changelog',
                icon: 'fas fa-circle-plus',
                url: $this->generateUrl('changelog')
            )
        ];
    }

    private function isFeatureEnabled(mixed $key): bool
    {
        if (array_key_exists($key, $this->statbusFeatures)) {
            return $this->statbusFeatures[$key];
        } else {
            return true; //Link isn't tracked by feature flags
        }
    }
}

class MenuItem
{
    public function __construct(
        public string $title,
        public string $icon,
        public string $url,
        public ?string $btn = 'btn-primary',
        public ?string $img = null
    ) {}
}
