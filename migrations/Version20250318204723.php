<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250318204723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE `allow_list` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ckey` varchar(32) NOT NULL,
            `admin` varchar(32) NOT NULL,
            `adminrank` varchar(32) NOT NULL,
            `datetime` datetime NOT NULL DEFAULT current_timestamp(),
            `expiration` datetime NOT NULL,
            `reason` longtext NOT NULL,
            `revoked` varchar(32) DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE `allow_list`");
    }
}
