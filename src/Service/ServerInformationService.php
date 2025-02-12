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

    public function getServers(): ?array
    {
        if (empty($this->servers)) {
            $this->fetchServers();
        }
        return $this->servers;
    }

    private function fetchServers(): void
    {
        if (file_exists(dirname(__DIR__) . '/../servers.json')) {
            $json = file_get_contents(dirname(__DIR__) . '/../servers.json');
        } else {
            $json = file_get_contents(dirname(__DIR__) . '/../servers.json.example');
        }
        $this->servers = json_decode($json, true);
        foreach ($this->servers as &$s) {
            $s = new Server(
                name: $s['name'],
                identifier: $s['dbname'],
                port: $s['port'],
                publicLogs: $s['publicLogsUrl'],
                rawLogs: $s['rawLogsUrl'],
                address: $s['address'],
                round: 0
            );
        }
        $content = $this->fetchRemoteServerInformation();
        if ([] === $content) {
            return;
        }
        foreach ($this->servers as $k => &$s) {
            if (
                !empty($content['servers'][$s->getUrl()])
                && $content['servers'][$s->getUrl()]['version'] === $this->gameVersion
            ) {
                $s->setRound($content['servers'][$s->getUrl()]['round_id']);
                $this->currentRounds[] = $content['servers'][$s->getUrl()]['round_id'];
            } else {
                unset($this->servers[$k]);
            }
        }
        sort($this->currentRounds);
    }

    public function getServerFromPort(int $port): ?Server
    {
        if (!$this->servers) {
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
        if (empty($this->servers)) {
            $this->fetchServers();
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

    public function getCurrentRounds(): ?array
    {
        return $this->currentRounds;
    }

    private function fetchRemoteServerInformation(): array
    {

        $cache = new FilesystemAdapter();
        $data = $cache->get('server_information', function (ItemInterface $item): array {
            try {
                $item->expiresAfter(300); // five minutes
                $response = $this->client->request(
                    'GET',
                    $_ENV['SERVER_INFO_ENDPOINT'],
                    [
                        'timeout' => 1
                    ]
                );
                return $response->toArray();
            } catch (Exception $e) {
                return [];
            }
        });
        return $data;
    }
}
