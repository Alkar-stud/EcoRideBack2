<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507205029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE vehicle (id SERIAL NOT NULL, energy_id INT NOT NULL, owner_id INT NOT NULL, brand VARCHAR(50) NOT NULL, model VARCHAR(50) NOT NULL, color VARCHAR(50) NOT NULL, license_plate VARCHAR(20) NOT NULL, license_first_date DATE NOT NULL, bn_place INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1B80E486EDDF52D ON vehicle (energy_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1B80E4867E3C61F9 ON vehicle (owner_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN vehicle.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN vehicle.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486EDDF52D FOREIGN KEY (energy_id) REFERENCES energy (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4867E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E486EDDF52D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle DROP CONSTRAINT FK_1B80E4867E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vehicle
        SQL);
    }
}
