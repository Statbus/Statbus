<?php

namespace App\Controller;

use App\Entity\Search;
use App\Enum\Poll\Type;
use App\Repository\PollRepository;
use App\Service\Poll\TallyIRVPollService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PollController extends AbstractController
{

    public function __construct(
        private PollRepository $pollRepository
    ) {}

    #[Route('/polls/{page}', name: 'polls')]
    public function polls(int $page = 1): Response
    {
        $polls = $this->pollRepository->getFinishedPolls($page);
        return $this->render('poll/index.html.twig', [
            'polls' => $polls,
            'pager' => $this->pollRepository->getPager()
        ]);
    }

    #[Route('/poll/{id}', name: 'poll')]
    public function poll(int $id, Request $request): Response
    {
        $search = Search::fromRequest($request);
        $poll = $this->pollRepository->getPoll($id, $search);

        return $this->render('poll/poll.html.twig', [
            'poll' => $poll,
            'search' => $search
        ]);
    }
}
