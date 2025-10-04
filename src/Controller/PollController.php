<?php

namespace App\Controller;

use App\Attribute\FeatureEnabled;
use App\Entity\Player;
use App\Entity\Rank;
use App\Entity\Search;
use App\Entity\Vote;
use App\Enum\Poll\Type;
use App\Repository\PollRepository;
use App\Service\Poll\TallyIRVPollService;
use App\Service\RankService;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[FeatureEnabled('polls')]
final class PollController extends AbstractController
{
    public function __construct(
        private PollRepository $pollRepository
    ) {}

    #[Route('/polls/{page}', name: 'polls', priority: 1)]
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
            'search' => $search,
            'breadcrumb' => [
                'Polls' => $this->generateUrl('polls'),
                '#' . $poll->getId() => $this->generateUrl(
                    'poll',
                    ['id' => $poll->getId()]
                )
            ]
        ]);
    }

    #[Route(
        '/polls/adhoc',
        name: 'adhoc',
        methods: ['GET', 'POST'],
        priority: 20
    )]
    public function adhoc(Request $request): Response
    {
        $votes = [];
        if ($request->isMethod('POST')) {
            $data = json_decode($request->request->get('data'))->data;
            foreach ($data as $d) {
                $votes[] = new Vote(
                    $d->optionid,
                    Player::newDummyPlayer($d->ckey, Rank::getPlayerRank()),
                    $d->text,
                    new DateTimeImmutable()
                );
            }
            $poll = $this->pollRepository->getPoll(
                382,
                Search::fromRequest($request),
                $votes
            );
            $poll = TallyIRVPollService::tally($poll);
            return $this->render('poll/poll.html.twig', [
                'poll' => $poll
            ]);
        }
        return $this->render('poll/adhoc.html.twig');
    }
}
