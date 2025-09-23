<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Round;
use App\Enum\Roles\Jobs;
use DateTimeImmutable;
use DateTimeInterface;

class ManifestRepository extends TGRepository
{
    public const TABLE = 'manifest';
    public const ALIAS = 'm';

    public const ENTITY = ManifestEntry::class;

    public const COLUMNS = [
        'm.ckey',
        'm.character_name as `character`',
        'm.job',
        'm.special',
        'm.latejoin',
        'm.timestamp'
    ];

    public function fetchPlayerCharacters(Player|string $player): array
    {
        $ckey = $player;
        if ($player instanceof Player) {
            $ckey = $player->getCkey();
        }
        $qb = $this->qb();
        $qb
            ->select(
                'm.character_name as `character`',
                'count(m.round_id) as rounds'
            )
            ->from(static::TABLE, static::ALIAS)
            ->where('m.ckey = ' . $qb->createNamedParameter($ckey))
            ->orderBy('rounds', 'DESC')
            ->groupBy('m.character_name');
        $results = $qb->executeQuery()->fetchAllAssociative();
        return $results;
    }

    public function fetchRoundManifest(Round $round): array
    {
        $qb = $this->qb();
        $qb
            ->select(...static::COLUMNS)
            ->from(static::TABLE, static::ALIAS)
            ->where('m.round_id = ' .
                $qb->createNamedParameter($round->getId()))
            ->orderBy('m.timestamp', 'DESC');
        $results = $qb->executeQuery()->fetchAllAssociative();
        foreach ($results as &$r) {
            if ('NONE' === $r['special']) {
                $r['special'] = null;
            }
            $r = new ManifestEntry(
                ckey: $r['ckey'],
                character: $r['character'],
                job: Jobs::tryFrom($r['job']),
                timestamp: new DateTimeImmutable($r['timestamp']),
                special: Jobs::tryFrom($r['special']),
                latejoin: $r['latejoin']
            );
        }
        return $results;
    }

    public function search(string $term): array
    {
        $qb = $this->connection->createQueryBuilder();
        $result = $qb
            ->select('ckey', 'character_name', 'count(id) as count')
            ->from('manifest')
            ->where($qb->expr()->like('character_name', ':term'))
            ->setParameter('term', "%$term%")
            ->orderBy('count', 'DESC')
            ->executeQuery();
        return $result->fetchAllAssociative();
    }
}

class ManifestEntry
{
    public function __construct(
        public Player|string $ckey,
        public string $character,
        public Jobs $job,
        public DateTimeInterface $timestamp,
        public ?Jobs $special = null,
        public bool $latejoin = false
    ) {
    }
}
