<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;

class DiscordVerificationsRepository extends ServiceEntityRepository
{
    public function __construct(
        private Connection $connection
    ) {}

    public function getDiscordVerificationsForCkey(Player $player): array
    {
        $qb = $this->connection->createQueryBuilder();
        $result = $qb
            ->select('d.ckey', 'd.discord_id', 'd.timestamp', 'd.valid')
            ->from('discord_links', 'd')
            ->where('d.ckey =' . $qb->createNamedParameter($player->getCkey()))
            ->executeQuery()
            ->fetchAllAssociative();
        return $result;
    }

    public function getCkeyFromDiscordId(int $id): ?string
    {
        $qb = $this->connection->createQueryBuilder();
        return $qb
            ->select('d.ckey')
            ->from('discord_links', 'd')
            ->where('d.discord_id = ' . $qb->createNamedParameter($id))
            ->andWhere('d.valid = 1')
            ->executeQuery()
            ->fetchOne();
    }
}
