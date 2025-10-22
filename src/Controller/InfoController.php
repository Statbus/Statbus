<?php

namespace App\Controller;

use App\Repository\ConnectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/info', name: 'info')]
final class InfoController extends AbstractController
{
    public function __construct(
        private ConnectionRepository $connectionRepository
    ) {}

    #[Route('/connections', name: '.connections')]
    public function index(Request $request): Response
    {
        $key = 'year';
        $value = $request->query->get('year', null);
        $range = "for $value";
        return $this->render('info/connections.html.twig', [
            'years' => $this->connectionRepository->fetchConnectionYearRange(),
            'range' => $range,
            'connections' => $this->connectionRepository->fetchRecentConnectionCounts(
                $key,
                $value
            )
        ]);
    }

    #[Route('/hourly', name: '.hourly')]
    public function hourlyChart(): Response
    {
        $data = $this->connectionRepository->getHourlyChartData();
        return $this->render('info/hourly.html.twig', [
            'data' => $data
        ]);
    }
}
