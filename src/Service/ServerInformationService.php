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
        $servers = json_decode($json, true);
        //Grab our list of default servers and convert the entries into a list
        //of Server entities
        foreach ($servers as $s) {
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
        //Get the remote server information
        $content = $this->fetchRemoteServerInformation();
        if ([] === $content) {
            return;
        }
        $remoteServers = [];
        foreach ($content['servers'] as $s) {
            //Discard any servers that don't match the game version we're 
            //looking for
            if ($s['data']['version'] === $this->gameVersion) {
                //Grab the current round ID
                $remoteServers[$s['identifier']] = $s['data']['round_id'];
            }
        }
        foreach ($remoteServers as $i => $r) {
            if (isset($this->servers[$i])) {
                //Set the current round ID on our list of servers that exist 
                //in the remote information
                $this->servers[$i]->setRound($r);
                $this->currentRounds[] = $r;
            }
        }
        foreach($this->servers as $k => $v){
            if(!$v->getRound()){
                //Discard any servers that don't have a round ID
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
