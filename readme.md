# Ecoride - BackEnd

## Présentation
Ce projet est réalisé dans le cadre de l'ECF de Studi.

## Pré-requis
* Docker doit être installé sur la machine hôte

## Installation
1. Cloner le projet
2. Créer un fichier ".env.docker" à la racine du projet en prenant comme modèle le ".env"
3. Dans le répertoire du projet, lancer `docker compose up -d --build`
4. Puis `docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction`


Une fois l'installation terminée, le backend est disponible ici http://localhost:8000.
Vous avez accès à un gestionnaire de PostGreSQL à l'adresse http://localhost:8081,  
et un accès à un gestionnaire MongoDB à l'adresse http://localhost:8082. 

Le dossier local étant monté dans le conteneur, le développement se fait sans avoir à reconstruire l'image