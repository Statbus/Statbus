<?php

namespace App\Controller;

use App\Entity\MenuItem;
use App\Repository\PopulationRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/population', name: 'population')]
final class PopulationController extends AbstractController
{
    public function __construct(
        private PopulationRepository $populationRepository
    ) {}

    #[Route('/yearly/{year}', name: '.yearly')]
    public function index(int $year = 2025): Response
    {
        return $this->render('population/yearly.html.twig', [
            'links' => $this->generateLinks('connections'),
            'years' => $this->populationRepository->fetchPopulationYearRange(),
            'year' => $year,
            'data' => $this->populationRepository->getYearlyChartData($year)
        ]);
    }

    #[Route('/hourly', name: '.hourly')]
    public function hourlyChart(): Response
    {
        $data = $this->populationRepository->getHourlyChartData();
        return $this->render('population/hourly.html.twig', [
            'links' => $this->generateLinks('hourly'),
            'data' => $data
        ]);
    }

    private function generateLinks(?string $active = null): array
    {
        $links = [
            'connections' => new MenuItem(
                title: 'Connection Data',
                icon: 'fas fa-chart-line',
                url: $this->generateUrl(
                    'population.yearly',
                    ['year' => (new DateTimeImmutable())->format('Y')]
                )
            ),
            'hourly' => new MenuItem(
                title: 'Population By Hour',
                icon: 'fas fa-clock',
                url: $this->generateUrl('population.hourly')
            )
        ];
        foreach ($links as $k => &$l) {
            if ($k === $active) {
                $l->btn .= ' active';
            }
        }
        return $links;
    }
}
