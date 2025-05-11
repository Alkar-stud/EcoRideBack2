<?php
namespace App\Factory;

use App\Entity\User;
use App\Entity\Preferences;
use DateTimeImmutable;

class UserFactory
{
    public function createUser(string $email, string $pseudo, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPseudo($pseudo);
        $user->setPassword($password);
        //aucun role ne peut être attribué à la création
        $user->setRoles([]);
        $user->setCreatedAt(new DateTimeImmutable());

        // Ajout des préférences par défaut
        //On ajoute 2 préférences 'smokingAllowed' et 'petsAllowed' avec en description 'no'
        $this->addDefaultPreferences($user);

        return $user;
    }

    private function addDefaultPreferences(User $user): void
    {
        $smokingPreference = new Preferences();
        $smokingPreference->setLibelle('smokingAllowed');
        $smokingPreference->setDescription('no');
        $smokingPreference->setUserPreferences($user);
        $smokingPreference->setCreatedAt(new DateTimeImmutable());

        $petsPreference = new Preferences();
        $petsPreference->setLibelle('petsAllowed');
        $petsPreference->setDescription('no');
        $petsPreference->setUserPreferences($user);
        $petsPreference->setCreatedAt(new DateTimeImmutable());

        $user->addPreference($smokingPreference);
        $user->addPreference($petsPreference);
    }
}