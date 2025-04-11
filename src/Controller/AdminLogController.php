<?php

namespace App\Controller;

use App\Repository\AdminLogRepository;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/info/admin')]
class AdminLogController extends AbstractController
{

    public function __construct(
        private AdminLogRepository $adminLogRepository,
        private PlayerRepository $playerRepository
    ) {}

    #[Route('/logs/{page}', name: 'app.admin_log')]
    public function index(int $page = 1): Response
    {
        $paginator = $this->adminLogRepository->getAdminLogs($page);
        return $this->render('admin_log/index.html.twig', [
            'paginator' => $paginator,
            'logs' => $paginator->getItems()
        ]);
    }

    #[Route('/roster', name: 'app.admin_roster')]
    public function roster(): Response
    {
        $roster = $this->playerRepository->getAdmins();
        return $this->render('admin_log/roster.html.twig', [
            'roster' => $roster
        ]);
    }

    #[Route('/roster/v1')]
    public function rosterAPI(): Response
    {
        $roster = $this->playerRepository->getAdmins();
        return $this->json(
            $roster
        );
    }
}
