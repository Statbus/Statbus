<?php

namespace App\Controller;

use App\Repository\AdminLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/info/admin')]
class AdminLogController extends AbstractController
{

    public function __construct(
        private AdminLogRepository $adminLogRepository
    ) {
    }

    #[Route('/logs/{page}', name: 'app.admin_log')]
    public function index(int $page = 1): Response
    {
        $paginator = $this->adminLogRepository->getAdminLogs($page);
        return $this->render('admin_log/index.html.twig', [
            'paginator' => $paginator,
            'logs' => $paginator->getItems()
        ]);
    }
}
