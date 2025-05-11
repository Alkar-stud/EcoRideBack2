<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511093831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ride (id INT AUTO_INCREMENT NOT NULL, driver_id INT NOT NULL, vehicle_id INT NOT NULL, starting_address VARCHAR(255) NOT NULL, arrival_address VARCHAR(255) NOT NULL, starting_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', duration INT NOT NULL, cost INT NOT NULL, max_nb_places INT NOT NULL, actual_departure_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', actual_arrival_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_9B3D7CD0C3423909 (driver_id), INDEX IDX_9B3D7CD0545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ride ADD CONSTRAINT FK_9B3D7CD0C3423909 FOREIGN KEY (driver_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ride ADD CONSTRAINT FK_9B3D7CD0545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ride DROP FOREIGN KEY FK_9B3D7CD0C3423909
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ride DROP FOREIGN KEY FK_9B3D7CD0545317D1
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ride
        SQL);
    }
}
