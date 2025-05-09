<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508220002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       $this->addSql(<<<'SQL'
            INSERT INTO user (email, roles, password, pseudo, credits, api_token, is_active, created_at) VALUES ('admin@ecoride.fr', '["admin"]', '$2y$13$r.iH55Y3TpA3MJKo7DeMpu0n1h1nYEBsBltwWuQTHR1r9rN/.btUS', 'Admin', 0, '86135ae9-54e8-46a7-997c-f3dd345d8b5d48cada49fd53e8b20070', true, NOW())
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO eco_ride (libelle, parameters, created_at) VALUES ('TOTAL_CREDIT', '0', NOW()), ('WELCOME_CREDIT', '20', NOW()), ('DEFAULT_TRIP_STATUS_ID', '1', NOW()), ('COST_EACH_RIDE', '2', NOW()), ('DEFAULT_NOTICE_STATUS_ID', '1', NOW()), ('FINISHED_TRIP_STATUS_ID', '4', NOW())
        SQL);
        
        $this->addSql(<<<'SQL'
            INSERT INTO `energy` (`libelle`, `is_eco`, `created_at`) VALUES ('Électrique', 1, NOW()), ('Hybride', NULL, NOW()), ('Énergie fossile', NULL, NOW())
        SQL);
        
        $this->addSql(<<<'SQL'
            INSERT INTO `mail` (`type_mail`, `subject`, `content`, `created_at`) VALUES ('cancel', 'EcoRide - Annulation du covoiturage du {date} vers {arrivalAddress}', 'Bonjour {pseudo}, <br> le covoitugrage a été annulé.', NOW()), ('passengerValidation', 'EcoRide - Comment s&#039;est passé votre covoiturage ?', 'Bonjour {pseudo}, &lt;br&gt; Covoiturage du {date} vers &lt;b&gt;{arrivalAddress}&lt;/b&gt;.&lt;br&gt;Vous devez le valider et si vous souhaitez donner votre avis en &lt;a href=&quot;{host}/giveYourOpinion?{id}&quot;&gt;cliquant ici&lt;/a&gt;.&lt;br&gt;Vous pouvez aussi nous indiquer s&#039;il s&#039;est mal passé.&lt;br&gt;&lt;br&gt;À bientôt !', NOW()), ('accountUserCreate', 'EcoRide - Bienvenue chez nous', 'Bienvenue chez nous !', NOW()), ('forgotPasword', 'EcoRide - Vous avez oublié votre mot de passe ?', 'Bonjour, <br />br&gt;veuillez trouver ci dessous votre mot de passe temporaire.', NOW()), ('changeTripVehicle', 'EcoRide - Changement de véhicule pour le covoiturage', 'Bonjour {pseudo}, \n<br>\nle chauffeur a changé de véhicule pour un {brand} {model} de couleur {color}.\nLe tarif reste inchangé.', NOW())
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
        
        $this->addSql(<<<'SQL'
            TRUNCATE TABLE energy
        SQL);
        
        $this->addSql(<<<'SQL'
            TRUNCATE TABLE mail
        SQL);
    }
}
