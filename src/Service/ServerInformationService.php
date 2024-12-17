<?php

namespace App\Service;

use App\Entity\Server;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServerInformationService
{

    private array $servers;

    public function __construct(
        private HttpClientInterface $client,
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

    private function fetchServers(): void
    {
        $response = $this->client->request(
            'GET',
            $_ENV['SERVER_INFO_ENDPOINT']
        );
        $content = $response->toArray();
        foreach ($content as $c) {
            if (isset($c['version'])) {
                if ($c['version'] != '/tg/Station 13') {
                    continue;
                }
                $this->servers[] = new Server(
                    $c['serverdata']['servername'],
                    $c['identifier'],
                    $c['serverdata']['port'],
                    isset($c['serverdata']['public_logs_url']) ? $c['serverdata']['public_logs_url'] : null,
                    isset($c['serverdata']['raw_logs_url']) ? $c['serverdata']['raw_logs_url'] : null,
                    isset($c['round_id']) ? $c['round_id'] : null
                );
            }
        }
    }

    public function getServerFromPort(int $port): ?Server
    {
        if (empty($this->servers)) {
            $this->fetchServers();
        }
        foreach ($this->servers as $server) {
            if ($server->getPort() === $port) {
                return $server;
            }
        }
        return null;
    }
}
