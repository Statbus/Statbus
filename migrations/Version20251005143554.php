<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005143554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Election SQL schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE `election` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `creator` varchar(255) NOT NULL,
            `created` datetime NOT NULL DEFAULT current_timestamp(),
            `start` datetime NOT NULL,
            `end` datetime NOT NULL,
            `anonymity` varchar(32) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;'
        );
        $this->addSql(
            'CREATE TABLE `candidate` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `election` int(11) unsigned NOT NULL,
            `name` varchar(255) NOT NULL,
            `link` varchar(255) DEFAULT NULL,
            `description` longtext DEFAULT NULL,
            `created` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `election` (`election`),
            CONSTRAINT `candidate_ibfk_1` FOREIGN KEY (`election`) REFERENCES `election` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;'
        );

        $this->addSql(
            'CREATE TABLE `vote` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `election` int(11) unsigned NOT NULL,
            `ckey` varchar(255) NOT NULL,
            `ballot_by_id` longtext NOT NULL,
            `ballot_by_name` longtext NOT NULL,
            `cast` datetime NOT NULL DEFAULT current_timestamp(),
            `type` varchar(255) DEFAULT NULL,
            `filterHash` varchar(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `ckey` (`ckey`,`election`),
            KEY `election` (`election`),
            CONSTRAINT `vote_ibfk_1` FOREIGN KEY (`election`) REFERENCES `election` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
