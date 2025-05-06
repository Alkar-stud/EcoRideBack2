<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506182600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD pseudo VARCHAR(255) NOT NULL, ADD photo VARCHAR(255) DEFAULT NULL, ADD credits INT NOT NULL, ADD grade TINYINT(1) DEFAULT NULL, ADD is_driver TINYINT(1) DEFAULT NULL, ADD is_passenger TINYINT(1) DEFAULT NULL, ADD api_token VARCHAR(64) NOT NULL, ADD is_active TINYINT(1) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP pseudo, DROP photo, DROP credits, DROP grade, DROP is_driver, DROP is_passenger, DROP api_token, DROP is_active, DROP created_at, DROP updated_at
        SQL);
    }
}
