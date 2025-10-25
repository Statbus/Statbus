<?php

namespace App\Service;

use App\Entity\Server;
use Exception;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServerInformationService
{
    private ?array $servers;
    private array $currentRounds = [];

    public function __construct(
        private HttpClientInterface $client,
        private string $gameVersion
    ) {
        $this->fetchServers();
    }

    public function getServers(): array
    {
        if (empty($this->servers)) {
            $this->fetchServers();
        }
        return $this->servers;
    }

    private function fetchServers(bool $useCache = false): void
    {
        $jsonFile = dirname(__DIR__) . '/../servers.json';
        $json = file_exists($jsonFile)
            ? file_get_contents($jsonFile)
            : file_get_contents(dirname(__DIR__) . '/../servers.json.example');

        $serverData = json_decode($json, true);
        $this->servers = [];

        foreach ($serverData as $s) {
            $this->servers[strtolower($s['dbname'])] = new Server(
                name: $s['name'],
                identifier: $s['dbname'],
                port: $s['port'],
                publicLogs: $s['publicLogsUrl'],
                rawLogs: $s['rawLogsUrl'],
                address: $s['address'],
                round: null
            );
        }
        if ($useCache) {
            dump('Using cached server data');
            return;
        }

        $remoteServers = $this->fetchRemoteServerInformation();
        if (empty($remoteServers['servers'])) {
            return;
        }

        $this->currentRounds = [];
        foreach ($remoteServers['servers'] as $s) {
            if (empty($s['data'])) {
                continue;
            }
            if ($s['data']['version'] !== $this->gameVersion) {
                continue;
            }
            $identifier = $s['identifier'];
            $roundId = $s['data']['round_id'];
            if (isset($this->servers[$identifier])) {
                $this->servers[$identifier]->setRound($roundId);
                $this->currentRounds[] = $roundId;
            }
        }

        $this->servers = array_filter(
            $this->servers,
            fn(Server $server) => $server->getRound()
        );
        sort($this->currentRounds);
    }

    public function getServerFromPort(
        int $port,
        bool $useCached = false
    ): ?Server {
        // if (!$this->servers) {
        //     return new Server(
        //         name: 'Unknown',
        //         identifier: 'Unknown Server',
        //         port: $port,
        //         address: 'localhost',
        //         rawLogs: null,
        //         publicLogs: null,
        //         round: null
        //     );
        // }
        if (empty($this->servers)) {
            $this->fetchServers($useCached);
        }
        foreach ($this->servers as $server) {
            if ($server->getPort() === $port) {
                return $server;
            }
        }
        return new Server(
            name: 'Unknown',
            identifier: 'Unknown Server',
            port: $port,
            address: 'localhost',
            rawLogs: null,
            publicLogs: null,
            round: null
        );
    }

    public function getServerByIdentifier(string $identifier): ?Server
    {
        if (!$this->servers) {
            return new Server(
                name: 'Unknown',
                identifier: 'Unknown Server',
                port: 6666,
                address: 'localhost',
                rawLogs: null,
                publicLogs: null,
                round: null
            );
        }
        if (empty($this->servers)) {
            $this->fetchServers();
        }
        foreach ($this->servers as $server) {
            if ($server->getIdentifier() === $identifier) {
                return $server;
            }
        }
        return new Server(
            name: 'Unknown',
            identifier: 'Unknown Server',
            port: 6666,
            address: 'localhost',
            rawLogs: null,
            publicLogs: null,
            round: null
        );
    }

    public function getCurrentRounds(): array
    {
        return $this->currentRounds;
    }

    private function fetchRemoteServerInformation(): array
    {
        $cache = new FilesystemAdapter();

        return $cache->get(
            'server_information',
            function (ItemInterface $item): array {
                try {
                    $item->expiresAfter(300); // five minutes
                    $response = $this->client->request(
                        'GET',
                        $_ENV['SERVER_INFO_ENDPOINT'],
                        ['timeout' => 1]
                    );
                    $data = $response->toArray();
                    return !empty($data)
                        ? $data
                        : throw new Exception('Empty server data');
                } catch (Exception $e) {
                    return []; // Do not cache empty results
                }
            }
        );
    }

    public function getEmptyServer(int $port = 1): Server
    {
        return new Server(
            name: 'Unknown',
            identifier: 'Unknown Server',
            port: $port,
            address: 'localhost',
            rawLogs: null,
            publicLogs: null,
            round: null
        );
    }
}
