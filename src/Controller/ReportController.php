<?php

namespace App\Controller;

use App\Entity\Report;
use App\Enum\Report\Status;
use App\Repository\PlayerRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/report')]
class ReportController extends AbstractController
{

    public function __construct(
        private PlayerRepository $playerRepository
    ) {}

    #[Route('/new', name: 'report.new', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $target = $request->request->get('ckey');
        $target = $this->playerRepository->findByCkey($target);
        if ($target->getCkey() === $this->getUser()->getCkey()) {
            throw new Exception("You can't do that, nerd");
        }
        $report = new Report(
            id: 1,
            status: Status::DRAFT
        );
        return $this->render('report/index.html.twig', [
            'target' => $target,
            'report' => $report
        ]);
    }
}
