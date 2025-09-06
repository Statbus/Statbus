<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250906134104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the character image table to Statbus';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE `character_image` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ckey` varchar(32) NOT NULL,
  `character_name` varchar(255) NOT NULL,
  `image` longtext NOT NULL,
  `modified` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ckey` (`ckey`,`character_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;'
        );
    }

    public function down(Schema $schema): void
    {
        //No down migrations #yolo
    }
}
