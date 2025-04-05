<?php

namespace App\Controller;

use App\Form\CandidateType;
use App\Form\ElectionType;
use App\Service\Election\ElectionService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/election', name: 'election')]
final class ElectionController extends AbstractController
{

    public function __construct(
        private ElectionService $electionService
    ) {}

    #[Route('', name: '')]
    public function index(): Response
    {
        $elections = $this->electionService->getActiveElections();
        dump($elections);
        return $this->render('election/index.html.twig', [
            'elections' => $elections
        ]);
    }

    #[Route('/create', name: '.create')]
    #[IsGranted('ROLE_ELECTION')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(ElectionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $id = $this->electionService->createNewElection(
                name: $data['name'],
                start: $data['start'],
                end: $data['end'],
                creator: $this->getUser()
            );
            return $this->redirectToRoute('election.candidates', ['election' => $id]);
        }
        return $this->render('election/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/{election}/candidates', name: '.candidates')]
    #[IsGranted('ROLE_ELECTION')]
    public function candidates(int $election, Request $request): Response
    {

        $election = $this->electionService->getElection($election);
        $form = $this->createForm(CandidateType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$election->started() && !$election->over()) {
            $data = $form->getData();
            $this->electionService->addCandidate(
                election: $election,
                name: $data['name'],
                link: $data['link'],
                description: $data['description']
            );
            return $this->redirectToRoute('election.candidates', ['election' => $election->getId()]);
        }
        return $this->render('election/candidates.html.twig', [
            'form' => $form->createView(),
            'election' => $election
        ]);
    }

    #[Route('/{election}', name: '.single')]
    public function single(int $election): Response
    {
        $election = $this->electionService->getElection($election);
        if ($election->started() && !$election->over()) {
            if ($this->electionService->hasUserVotedInThisElection($this->getUser(), $election)) {
                throw new Exception("You have already voted in this election", 403);
            }
            return $this->render('election/vote.html.twig', [
                'election' => $election
            ]);
        } elseif ($election->over()) {
            return $this->render('election/result.html.twig', [
                'election' => $election
            ]);
        } elseif ($this->isGranted('ROLE_ELECTION')) {
            return $this->render('election/view.html.twig', [
                'election' => $election
            ]);
        }
        return $this->redirectToRoute('election');
    }

    #[Route('/{election}/vote', name: '.vote', methods: ['POST'])]
    public function vote(int $election, Request $request): Response
    {
        $election = $this->electionService->getElection($election);
        if ($this->electionService->hasUserVotedInThisElection($this->getUser(), $election)) {
            throw new Exception("You have already voted in this election", 403);
        }
        $this->electionService->castVote(
            vote: $request->request->all(),
            user: $this->getUser(),
            election: $election
        );
        return $this->json(['status' => 'okay']);
    }
}
