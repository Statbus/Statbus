<?php

namespace App\Service\Player;

use App\Entity\Player;
use App\Repository\DiscordVerificationsRepository;
use DateTimeImmutable;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

class DiscordVerificationsService
{
    public function __construct(
        private DiscordVerificationsRepository $discordVerificationsRepository,
        private HttpClientInterface $client
    ) {}

    public function findVerificationsForPlayer(Player $player): array
    {
        $data =
            $this->discordVerificationsRepository->getDiscordVerificationsForCkey(
                $player
            );
        foreach ($data as &$d) {
            $d['discord_data'] = null;
            $d['timestamp'] = new DateTimeImmutable($d['timestamp']);
            if ((bool) $d['valid']) {
                $d['discord_data'] = $this->getDiscordUsername(
                    $d['discord_id']
                );
            }
        }
        return $data;
    }

    private function getDiscordUsername(int $id): DiscordResourceOwner
    {
        $res = $this->client->request(
            'GET',
            "https://discord.com/api/users/$id",
            [
                'headers' => [
                    'Authorization' => 'Bot ' . $_ENV['DISCORD_BOT_TOKEN']
                ]
            ]
        );
        return new DiscordResourceOwner(json_decode($res->getContent(), true));
    }
}
