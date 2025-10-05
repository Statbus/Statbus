<?php

namespace App\Repository;

use App\Entity\Election\Candidate;
use App\Entity\Election\Election;
use App\Entity\Election\Vote;
use App\Entity\Player;
use App\Entity\Rank;
use App\Enum\Election\AnonymityType;
use App\Enum\Election\VoteType;
use App\Security\User;
use DateTimeImmutable;

class ElectionRepository extends StatbusRepository
{
    public const TABLE = 'election';
    public const ALIAS = 'e';

    public const COLUMNS = [
        'e.id',
        'e.name',
        'e.start',
        'e.end',
        'e.created',
        'e.creator',
        'e.anonymity'
    ];

    private function parseResult(array $result): Election
    {
        return new Election(
            id: $result['id'],
            name: $result['name'],
            start: new DateTimeImmutable($result['start']),
            end: new DateTimeImmutable($result['end']),
            creator: Player::newDummyPlayer(
                $result['creator'],
                Rank::getPlayerRank()
            ),
            created: new DateTimeImmutable($result['created']),
            candidates: $this->fetchCandidates($result['id']),
            votes: $this->fetchVotes($result['id']),
            anonymity: AnonymityType::from($result['anonymity'])
        );
    }

    public function insertNewElection(
        string $name,
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        AnonymityType $anonymity,
        User $creator
    ) {
        $qb = $this->qb();
        $qb
            ->insert(static::TABLE)
            ->values([
                'name' => $qb->createNamedParameter($name),
                'start' => $qb->createNamedParameter($start->format(
                    'Y-m-d H:i:s'
                )),
                'end' => $qb->createNamedParameter($end->format('Y-m-d H:i:s')),
                'creator' => $qb->createNamedParameter($creator->getCkey()),
                'anonymity' => $qb->createNamedParameter($anonymity->value)
            ])
            ->executeStatement();
        return $this->statbusConnection->lastInsertId();
    }

    public function updateElectionRow(Election $election): void
    {
        $qb = $this->qb();
        $qb
            ->update(static::TABLE, static::ALIAS)
            ->set(
                'e.start',
                $qb->createNamedParameter(
                    $election->getStart()->format('Y-m-d 00:00:00')
                )
            )
            ->set(
                'e.end',
                $qb->createNamedParameter(
                    $election->getEnd()->format('Y-m-d 00:00:00')
                )
            )
            ->set('e.name', $qb->createNamedParameter($election->getName()))
            ->where('e.id = ' . $qb->createNamedParameter($election->getId()));
    }

    public function fetchElection(int $id): Election
    {
        $qb = $this->qb();
        $result = $qb
            ->select(...static::COLUMNS)
            ->from(static::TABLE, static::ALIAS)
            ->where('e.id = ' . $qb->createNamedParameter($id))
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
        return $this->parseResult($result);
    }

    public function fetchActiveElections(): ?array
    {
        $qb = $this->qb();
        $results = $qb
            ->select(...static::COLUMNS)
            ->from(static::TABLE, static::ALIAS)
            ->where('e.start <= NOW()')
            ->andWhere('e.end >= NOW()')
            ->executeQuery()
            ->fetchAllAssociative();
        if (!$results) {
            return null;
        }
        foreach ($results as &$result) {
            $result = $this->parseResult($result);
        }
        return $results;
    }

    public function fetchPastElections(): ?array
    {
        $qb = $this->qb();
        $results = $qb
            ->select(...static::COLUMNS)
            ->from(static::TABLE, static::ALIAS)
            ->where('e.start <= NOW()')
            ->andWhere('e.end <= NOW()')
            ->executeQuery()
            ->fetchAllAssociative();
        if (!$results) {
            return null;
        }
        foreach ($results as &$result) {
            $result = $this->parseResult($result);
        }
        return $results;
    }

    public function fetchUpcomingElections(): ?array
    {
        $qb = $this->qb();
        $results = $qb
            ->select(...static::COLUMNS)
            ->from(static::TABLE, static::ALIAS)
            ->where('e.start >= NOW()')
            ->andWhere('e.end >= NOW()')
            ->executeQuery()
            ->fetchAllAssociative();
        if (!$results) {
            return null;
        }
        foreach ($results as &$result) {
            $result = $this->parseResult($result);
        }
        return $results;
    }

    private function fetchCandidates(int $election): ?array
    {
        $qb = $this->qb();
        $results = $qb
            ->select('c.id', 'c.name', 'c.link', 'c.description', 'c.created')
            ->from('candidate', 'c')
            ->where('c.election = ' . $qb->createNamedParameter($election))
            ->orderBy('RAND()')
            ->executeQuery()
            ->fetchAllAssociative();
        if (!$results) {
            return null;
        }
        foreach ($results as &$r) {
            $r = new Candidate(
                id: $r['id'],
                name: $r['name'],
                link: $r['link'],
                description: $r['description'],
                created: new DateTimeImmutable($r['created'])
            );
        }
        return $results;
    }

    private function fetchVotes(int $election): ?array
    {
        $qb = $this->qb();
        $results = $qb
            ->select(
                'v.id',
                'v.ckey',
                'v.ballot_by_id',
                'v.ballot_by_name',
                'v.cast',
                'v.type'
            )
            ->from('vote', 'v')
            ->where('v.election = ' . $qb->createNamedParameter($election))
            ->executeQuery()
            ->fetchAllAssociative();
        if (!$results) {
            return null;
        }
        foreach ($results as &$r) {
            $r = new Vote(
                id: $r['id'],
                ckey: $r['ckey'],
                idBallot: $r['ballot_by_id'],
                nameBallot: $r['ballot_by_name'],
                cast: new DateTimeImmutable($r['cast'])
            );
        }
        return $results;
    }

    public function insertCandidate(
        Election $election,
        string $name,
        ?string $link = null,
        ?string $description = null
    ): void {
        $qb = $this->qb();
        $qb
            ->insert('candidate')
            ->values([
                'name' => $qb->createNamedParameter($name),
                'link' => $qb->createNamedParameter($link),
                'description' => $qb->createNamedParameter($description),
                'election' => $qb->createNamedParameter($election->getId())
            ])
            ->executeStatement();
    }

    public function deleteCandidate(Election $election, int $candidate): void
    {
        $qb = $this->qb();
        $qb
            ->delete('candidate')
            ->where('election = ' .
                $qb->createNamedParameter($election->getId()))
            ->andWhere('id =' . $qb->createNamedParameter($candidate))
            ->executeStatement();
    }

    public function insertVote(
        string $ballotById,
        string $ballotByName,
        User $voter,
        Election $election,
        VoteType $type,
        string $filterHash
    ): void {
        $ckeyVoteQb = $this->qb();
        $ckeyVoteQb
            ->insert('ckey_vote')
            ->values([
                'election' => $ckeyVoteQb->createNamedParameter($election->getId()),
                'ckey' => $ckeyVoteQb->createNamedParameter($voter->getCkey())
            ])
            ->executeStatement();
        $ckey = $voter->getCkey();
        switch ($election->getAnonymity()) {
            case AnonymityType::SEMI:
                $ckey = hash('sha512', $ckey);
                break;

            case AnonymityType::ANONYMOUS:
                $ckey = hash('sha512', $ckey . random_bytes(32));
                break;
        }
        $qb = $this->qb();
        $qb
            ->insert('vote')
            ->values([
                'ballot_by_id' => $qb->createNamedParameter($ballotById),
                'ballot_by_name' => $qb->createNamedParameter($ballotByName),
                'ckey' => $qb->createNamedParameter($ckey),
                'election' => $qb->createNamedParameter($election->getId()),
                'type' => $qb->createNamedParameter($type->value),
                'filterHash' => $qb->createNamedParameter($filterHash)
            ])
            ->executeStatement();
    }

    public function findUserVoteForElection(
        User $user,
        Election $election
    ): ?string {
        $qb = $this->qb();
        $qb
            ->select('ckey', 'election')
            ->from('ckey_vote')
            ->where('ckey = ' . $qb->createNamedParameter($user->getCkey()))
            ->andWhere('election = ' .
                $qb->createNamedParameter($election->getId()))
            ->executeQuery();
        $result = $qb->fetchOne();
        if (!$result) {
            return null;
        }
        return $result;
    }
}
