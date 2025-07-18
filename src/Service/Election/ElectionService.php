<?php

namespace App\Service\Election;

use App\Entity\Election\Election;
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
        private ElectionRepository $electionRepository
    ) {}

    public function createNewElection(
        string $name,
        DateTimeInterface $start,
        DateTimeInterface $end,
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
            creator: $creator
        );
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
        $this->electionRepository->insertCandidate(
            election: $election,
            name: $name,
            link: $link,
            description: $description
        );
    }

    public function getActiveElections(): ?array
    {
        return $this->electionRepository->fetchActiveElections();
    }

    public function getPastElections(): ?array
    {
        return $this->electionRepository->fetchPastElections();
    }

    public function castVote(array $vote, User $user, Election $election): void
    {
        $ballot = $this->formatBallot($vote);
        //TODO: This
        // $type = $this->determineVoterEligibility($user);
        $this->electionRepository->insertVote(
            ballotById: $ballot['candidateId'],
            ballotByName: $ballot['candidateName'],
            voter: $user,
            election: $election
        );
    }

    public function hasUserVotedInThisElection(
        User $user,
        Election $election
    ): bool {
        return (bool) $this->electionRepository->findUserVoteForElection(
            $user,
            $election
        );
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
