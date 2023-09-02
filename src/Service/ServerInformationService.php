<?php

namespace App\Service;

use App\Domain\Server\Data\Server;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Symfony\Component\Yaml\Yaml;

class ServerInformationService
{
    public const BASE_URL = 'https://tgstation13.org';
    public const PUBLIC_LOGS = self::BASE_URL."/parsed-logs";
    public const ADMIN_LOGS = self::BASE_URL."/raw-logs";

    public static function getServerInfo(): array
    {
        $stack = HandlerStack::create();
        $stack->push(new CacheMiddleware(), 'cache');
        $client = new Client([
            'base_uri' => 'https://tgstation13.org/',
            'timeout'  => 2.0,
            'handler' => $stack
        ]);
        try {
            $response = $client->get('/serverinfo.json');
            $data = json_decode($response->getBody(), true);
        } catch (Exception $e) {
            $data = Yaml::parseFile(__DIR__.'/../../assets/servers.json');
        }

        return $data;
    }

    public static function getServerFromPort(int $port, ?array $data = null): ?Server
    {
        if(!$data) {
            $data = self::getServerInfo();
        }
        foreach($data as $server) {
            if(isset($server['serverdata'])) {
                if($port === $server['serverdata']['port']) {
                    $server = $server['serverdata'];
                    return new Server(
                        $server['dbname'],
                        $server['address'],
                        $server['port'],
                        $server['dbname'],
                        $server['publiclogsurl'],
                        $server['rawlogsurl']
                    );
                }
            }
        }
        return null;
    }

    public static function getServerFromName(string $name, ?array $data = null): ?Server
    {
        if(!$data) {
            $data = self::getServerInfo();
        }
        foreach($data as $server) {
            if(isset($server['identifier'])) {
                if($name === $server['identifier']) {
                    if(isset($server['identifier'])) {
                        $server['serverdata']['identifier'] = $server['identifier'];
                    } else {
                        $server['serverdata']['identifier'] = explode(' ', $server['serverdata']['servername'])[0];
                    }
                    return Server::fromArray($server['serverdata']);
                }
            }
        }
        return null;
    }

    public static function getCurrentRounds(?array $data = null): array
    {
        if(!$data) {
            $data = self::getServerInfo();
        }
        $rounds = [];
        foreach ($data as $s) {
            if(isset($s['version']) && $s['version'] === "/tg/Station 13") {
                if(isset($s['round_id'])) {
                    $rounds[] = (int) $s['round_id'];
                }
            }
        }
        return $rounds;
    }

}
