# Ecoride

## Presentation
This project is carried out within the framework of the ECF of Studi.

## Prerequisites
* apache2.4^, php 8.3^ and mysql8^ must be installed
* Check composer is installed

## Install
1. Clone this project
2. Extension php mongodb must be installed. Run if not `pecl install mongodb`
3. Run `composer install`
4. Run `symfony server:start -d` to launch your local php web server

## Database
1. Create a new ".env.local" file at the root of the project and copy the ".env" file content inside of it.
2. Comment the "postgresql" line (32) and uncomment the "sql" line (31)
3. Enter your username, your password and the name of your database on the dedicated places to configure the access of your sql database.
4. Run `php bin/console doctrine:database:create` to create your local database.
5. Run `php bin/console doctrine:maigrations:migrate` to update the database.
6. Run `php bin/console doctrine:fixtures:load` to load the fixtures.


Wait a moment and visit http://localhost:8000

## With docker
1. Run `docker compose up -d --build`
2. Run `docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction`