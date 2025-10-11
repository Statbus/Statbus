<?php

namespace App\Service\Round;

use App\Entity\Round;
use App\Entity\Stat;
use App\Repository\StatRepository;

class RoundStatsService
{
    public const STATBUS_GENERATED = [
        'qdel_log',
        'runtime_condensed',
        'telecomms',
        'dynamic',
        'attack'
    ];

    public function __construct(
        private StatRepository $statRepository,
        private RoundLogService $logs
    ) {}

    public function getRoundStats(Round $round, array $stats)
    {
        $results = [];
        $data = $this->statRepository->getStatsForRound($round, $stats);
        foreach ($data as $d) {
            $results[$d->getKey()] = $d;
        }
        return $results;
    }

    public function listStatsForRound(Round $round): array
    {
        return $this->statRepository->listStatsForRound($round);
    }

    public function getStatForRound(Round $round, string $stat): Stat
    {
        if (in_array($stat, static::STATBUS_GENERATED)) {
            return $this->generateStatbusStat($round, $stat);
        }
        return $this->statRepository->fetchStatForRound($round, $stat);
    }

    public function generateStatbusStat(Round $round, string $stat): Stat
    {
        switch ($stat) {
            case 'qdel_log':
                $data = $this->logs->getRemoteLogFile($round, 'qdel.log.json');
                $json = RoundLogService::jsonLinesToJson($data);
                break;
            case 'telecomms':
                $data = $this->logs->getRemoteLogFile(
                    $round,
                    'telecomms.log.json'
                );
                $json = RoundLogService::jsonLinesToJson($data);
                break;
            case 'runtime_condensed':
                $json = $this->logs->getRemoteLogFile(
                    $round,
                    'runtime.condensed.json'
                );
                break;
            case 'dynamic':
                $data = $this->logs->getRemoteLogFile(
                    $round,
                    'dynamic.log.json'
                );
                $json = RoundLogService::jsonLinesToJson($data);
                break;
            case 'attack':
                // $data = $this->logs->getRemoteLogFile(
                //     $round,
                //     'attack.log.json'
                // );
                // $json = FetchRoundLogService::jsonLinesToJson($data);
                $json = '';
                break;
        }

        $stat = new Stat(
            id: -1,
            datetime: $round->getInit(),
            round: $round->getId(),
            key: $stat,
            type: 'generated',
            version: 1,
            json: $json
        );
        return $stat;
    }
}
