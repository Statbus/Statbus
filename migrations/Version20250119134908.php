<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250119134908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE `public_ticket` (
            `round` int(11) unsigned NOT NULL,
            `ticket` int(11) unsigned NOT NULL,
            `identifier` varchar(255) NOT NULL,
            UNIQUE KEY `round` (`round`,`ticket`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 
            COLLATE=utf8mb4_general_ci;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE `public_ticket`;");
    }
}
