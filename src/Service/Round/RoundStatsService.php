<?php

namespace App\Service\Round;

use App\Entity\Round;
use App\Entity\Stat;
use App\Repository\StatRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class RoundStatsService
{
    public const STATBUS_GENERATED = [
        'qdel_log',
        'runtime_condensed',
        'telecomms',
        'dynamic',
        'attack'
    ];

    private FilesystemAdapter $cache;

    public function __construct(
        private StatRepository $statRepository,
        private RoundLogService $logs,
        private readonly string $storageDir
    ) {
        $this->cache = new FilesystemAdapter('', 0, $storageDir);
    }

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
        $stat = $this->cache->get($round->getId()
        . '-'
        . $stat, function (ItemInterface $item) use ($stat, $round): Stat {
            $url = $round->logUrl;
            switch ($stat) {
                case 'qdel_log':
                    $data = $this->logs->getRemoteLogFile(
                        $round,
                        'qdel.log.json'
                    );
                    $json = RoundLogService::jsonLinesToJson($data);
                    $url .= '/qdel.log.json';
                    break;
                case 'telecomms':
                    // $data = $this->logs->getRemoteLogFile(
                    //     $round,
                    //     'telecomms.log'
                    // );
                    // $json = RoundLogService::jsonLinesToJson($data);
                    $url .= '/telecomms.log';
                    $json = '[]';
                    break;
                case 'runtime_condensed':
                    $json = $this->logs->getRemoteLogFile(
                        $round,
                        'runtime.condensed.json'
                    );
                    $url .= '/runtime.condensed.json';
                    break;
                case 'dynamic':
                    $data = $this->logs->getRemoteLogFile(
                        $round,
                        'dynamic.log.json'
                    );
                    $json = RoundLogService::jsonLinesToJson($data);
                    $url .= '/dynamic.log.json';
                    break;
                case 'attack':
                    // $data = $this->logs->getRemoteLogFile(
                    //     $round,
                    //     'attack.log.json'
                    // );
                    // $json = FetchRoundLogService::jsonLinesToJson($data);
                    $url .= '/attack.log';
                    $json = '[]';
                    break;
            }

            $stat = new Stat(
                id: -1,
                datetime: $round->getInit(),
                round: $round->getId(),
                key: $stat,
                type: 'generated',
                version: 1,
                json: $json,
                originalUrl: $url
            );
            return $stat;
        });

        return $stat;
    }

    public function clearCachedStatsForRound(Round $round): void
    {
        $this->cache->clear($round->getId());
    }
}
