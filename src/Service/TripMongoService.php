<?php

namespace App\Service;

use DateTimeInterface;
use Exception;
use MongoDB\Client;
use MongoDB\Collection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TripMongoService
{
    private Collection $collection;

    public function __construct(string $mongoUri, string $databaseName)
    {
        $client = new Client($mongoUri);
        $this->collection = $client->selectCollection($databaseName, 'trips');
    }

    private function convertDatesToIsoFormat(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $data[$key] = $value->format(DateTimeInterface::ATOM);
            } elseif (is_array($value)) {
                $data[$key] = $this->convertDatesToIsoFormat($value);
            }
        }

        return $data;
    }

    public function add(array $data): void
    {
        // Conversion des dates en format ISO 8601 avant insertion
        $data = $this->convertDatesToIsoFormat($data);
        $this->collection->insertOne($data);
    }

    public function update(int $id, array $data): JsonResponse
    {
        try {
            // Conversion des dates en format ISO 8601 avant mise à jour
            $data = $this->convertDatesToIsoFormat($data);
            $result = $this->collection->updateOne(
                ['id_covoiturage' => $id],
                ['$set' => $data]
            );

            if ($result->getMatchedCount() === 0) {
                return new JsonResponse([
                    'error' => 'Covoiturage introuvable dans MongoDB.'
                ], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse([
                'message' => 'Covoiturage mis à jour avec succès dans MongoDB.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): bool
    {
        try {
            $result = $this->collection->deleteOne(['id_covoiturage' => $id]);

            return $result->getDeletedCount() > 0;

        } catch (Exception $e) {
            error_log("mongodb_delete_exception: Erreur lors de la suppression de l'id $id - " . $e->getMessage());
            return false;
        }
    }

    public function findById(int $id): ?array
    {
        $document = $this->collection->findOne(['id_covoiturage' => $id]);

        return $document?->getArrayCopy();
    }



    /**
     * Trouve les documents de voyage paginés dans MongoDB pour un utilisateur donné.
     *
     * @param int $userId L'ID de l'utilisateur.
     * @param int $page Le numéro de la page (commence à 1).
     * @param int $limit Le nombre de documents par page.
     * @return array Un tableau contenant 'data' (les documents de la page) et 'total' (le nombre total de documents).
     */
    public function findByUserId(int $userId, int $page = 1, int $limit = 10): array
    {
        try {
            // Assurer que la page et la limite sont valides
            $page = max(1, $page);
            $limit = max(1, $limit); // Limite minimale de 1

            // Calculer le nombre de documents à sauter (offset)
            $skip = ($page - 1) * $limit;

            // Le filtre cible le champ 'id' imbriqué dans le champ 'user'
            $filter = ['user.id' => $userId];

            // Options de recherche incluant tri, limite et skip
            $options = [
                'sort' => ['startingAt' => 1], // Trier par date de départ ascendante
                'limit' => $limit,
                'skip' => $skip,
            ];


            // Exécuter la requête pour obtenir les documents de la page courante
            $cursor = $this->collection->find($filter, $options);
            $results = iterator_to_array($cursor); // Convertit les résultats de la page en tableau

            // Compter le nombre total de documents correspondant au filtre (sans pagination)
            // C'est nécessaire pour que le client puisse calculer le nombre total de pages
            $totalDocuments = $this->collection->countDocuments($filter);

            return [
                'data' => $results, // Les documents de la page actuelle
                'total' => $totalDocuments, // Le nombre total de documents pour cet utilisateur
                'page' => $page,
                'limit' => $limit
            ];

        } catch (MongoDbDriverException $e) {
            // Gérer l'erreur de recherche
            error_log("MongoDB findByUserId (paginated) failed for user $userId: " . $e->getMessage());
            // Retourner une structure vide en cas d'erreur
            return [
                'data' => [],
                'total' => 0,
                'page' => $page, // Retourner la page demandée même en cas d'erreur peut être utile
                'limit' => $limit
            ];
        }
    }
}
