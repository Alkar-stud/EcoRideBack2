<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507141349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            INSERT INTO public.user (email, roles, password, pseudo, credits, api_token, is_active, created_at) VALUES ('admin@ecoride.fr', '["admin"]', '$2y$13$r.iH55Y3TpA3MJKo7DeMpu0n1h1nYEBsBltwWuQTHR1r9rN/.btUS', 'Admin', 0, '86135ae9-54e8-46a7-997c-f3dd345d8b5d48cada49fd53e8b20070', true, NOW())
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO public.eco_ride (libelle, parameters, created_at) VALUES ('TOTAL_CREDIT', '0', NOW()), ('WELCOME_CREDIT', '20', NOW()), ('DEFAULT_TRIP_STATUS_ID', '1', NOW()), ('COST_EACH_RIDE', '2', NOW()), ('DEFAULT_NOTICE_STATUS_ID', '1', NOW()), ('FINISHED_TRIP_STATUS_ID', '4', NOW())
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            TRUNCATE TABLE user
        SQL);

        $this->addSql(<<<'SQL'
            TRUNCATE TABLE eco_ride
        SQL);
    }
}
