<?php

namespace App\Service\Round;

use App\Entity\Round;
use Exception;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;

class RoundLogService
{
    private CachingHttpClient $client;

    public function __construct(
        private readonly string $storageDir
    ) {
        $store = new Store($storageDir);
        $client = HttpClient::create();
        $this->client = new CachingHttpClient($client, $store);
    }

    public function getRemoteLogFile(Round $round, string $file)
    {
        if (!$round->logUrl) {
            throw new Exception('This round does not appear to have logs', 404);
        }
        $uri = sprintf('%s/%s', $round->logUrl, $file);
        $response = $this->client->request('GET', $uri);
        return $response->getContent(false);
    }

    public static function jsonLinesToJson(string $data): string
    {
        $data = explode("\n", $data);
        $json = [];
        foreach ($data as $d) {
            $json[] = json_decode($d, true);
        }
        return json_encode($json);
    }
}
