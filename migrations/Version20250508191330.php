<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508191330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, pseudo VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, credits INT NOT NULL, grade INT DEFAULT NULL, is_driver TINYINT(1) DEFAULT NULL, is_passenger TINYINT(1) DEFAULT NULL, api_token VARCHAR(64) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE eco_ride (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) NOT NULL, parameters LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE energy (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) NOT NULL, is_eco TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE preferences (id INT AUTO_INCREMENT NOT NULL, user_preferences_id INT NOT NULL, libelle VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_E931A6F51748B0EE (user_preferences_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, energy_id INT NOT NULL, owner_id INT NOT NULL, brand VARCHAR(50) NOT NULL, model VARCHAR(50) NOT NULL, color VARCHAR(50) NOT NULL, license_plate VARCHAR(20) NOT NULL, license_first_date DATE NOT NULL, nb_place INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_1B80E486EDDF52D (energy_id), INDEX IDX_1B80E4867E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE preferences ADD CONSTRAINT FK_E931A6F51748B0EE FOREIGN KEY (user_preferences_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486EDDF52D FOREIGN KEY (energy_id) REFERENCES energy (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4867E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE preferences DROP FOREIGN KEY FK_E931A6F51748B0EE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E486EDDF52D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E4867E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `user`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE eco_ride
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE energy
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE preferences
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vehicle
        SQL);
    }
}
