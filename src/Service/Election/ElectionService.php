<?php

namespace App\Service\Election;

use App\Entity\Election\Election;
use App\Enum\Election\AnonymityType;
use App\Enum\Election\VoteType;
use App\Repository\ElectionRepository;
use App\Security\User;
use CondorcetPHP\Condorcet\Candidate;
use CondorcetPHP\Condorcet\Election as CondorcetElection;
use CondorcetPHP\Condorcet\Vote;
use DateTimeImmutable;
use DateTimeInterface;
use Dom\Element;
use Exception;

class ElectionService
{
    public function __construct(
        private ElectionRepository $electionRepository,
        private VoteFilterService $voteFilterService
    ) {}

    public function createNewElection(
        string $name,
        DateTimeInterface $start,
        DateTimeInterface $end,
        AnonymityType $anonymity,
        User $creator
    ): int {
        $start = DateTimeImmutable::createFromInterface($start);
        if ($start < new DateTimeImmutable()) {
            throw new Exception('Elections cannot start in the past');
        }
        $start = $start->setTime(0, 0, 0);
        $end = DateTimeImmutable::createFromInterface($end);
        if ($end < $start) {
            throw new Exception('Elections cannot end before they start');
        }
        $end = $end->setTime(23, 59, 59);
        return $this->electionRepository->insertNewElection(
            name: $name,
            start: $start,
            end: $end,
            creator: $creator,
            anonymity: $anonymity
        );
    }

    public function updateElection(Election $election): void
    {
        if ($election->isUnderway() || $election->over()) {
            throw new Exception('You cannot modify this election', 403);
        }
        $this->electionRepository->updateElectionRow($election);
    }

    public function getElection(int $id): Election
    {
        $election = $this->electionRepository->fetchElection($id);
        if ($election->over()) {
            $this->tallyResults($election);
        }
        return $election;
    }

    public function addCandidate(
        Election $election,
        string $name,
        ?string $link = null,
        ?string $description = null
    ) {
        if ($election->isUnderway() || $election->over()) {
            throw new Exception(
                'You cannot modify candidates for this election',
                403
            );
        }
        $this->electionRepository->insertCandidate(
            election: $election,
            name: $name,
            link: $link,
            description: $description
        );
    }

    public function removeCandidate(Election $election, int $candidate): void
    {
        if ($election->isUnderway() || $election->over()) {
            throw new Exception(
                'You cannot modify candidates for this election',
                403
            );
        }
        if (!in_array($candidate, array_keys($election->getCandidates('id')))) {
            throw new Exception(
                'This candidate is not a part of this election',
                401
            );
        }
        $this->electionRepository->deleteCandidate($election, $candidate);
    }

    public function getActiveElections(): ?array
    {
        return $this->electionRepository->fetchActiveElections();
    }

    public function getPastElections(): ?array
    {
        return $this->electionRepository->fetchPastElections();
    }

    public function getUpcomingElections(): ?array
    {
        return $this->electionRepository->fetchUpcomingElections();
    }

    public function castVote(array $vote, User $user, Election $election): void
    {
        $ballot = $this->formatBallot($vote);
        $type = $this->determineVoterEligibility($user, $election);
        $this->electionRepository->insertVote(
            ballotById: $ballot['candidateId'],
            ballotByName: $ballot['candidateName'],
            voter: $user,
            election: $election,
            type: $type,
            filterHash: $election->getFilterHash()
        );
    }

    private function determineVoterEligibility(
        User $user,
        Election $election
    ): VoteType {
        if (!$election->isUnderway()) {
            throw new Exception('This election is not open for voting', 403);
        }
        if ($this->hasUserVotedInThisElection($user, $election)) {
            throw new Exception('You have already voted in this election', 403);
        }
        return $this->voteFilterService->getVoterType($user, $election);
    }

    public function hasUserVotedInThisElection(
        User $user,
        Election $election
    ): bool {
        $vote = $this->electionRepository->findUserVoteForElection(
            $user,
            $election
        );
        return (bool) $vote;
    }

    private function formatBallot(array $vote): array
    {
        foreach ($vote as &$v) {
            $v = implode(' > ', $v);
        }
        return $vote;
    }

    private function tallyResults(Election $election)
    {
        $condorcetElection = new CondorcetElection();
        foreach ($election->getCandidates() as $c) {
            $condorcetElection->addCandidate(new Candidate($c->getName()));
        }
        foreach ($election->getVotes() as $v) {
            $condorcetElection->addVote(new Vote($v->getBallotByName()), [
                'ckey' => $v->getCkey()
            ]);
        }
        $election->setResult($condorcetElection->getResult('IRV'));
        $election->setWinner($condorcetElection->getWinner('IRV'));
        return $election;
    }
}
