<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vehicle;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\VehicleRepository;
use App\Repository\EnergyRepository;
use DateTimeImmutable;
use Nelmio\ApiDocBundle\Attribute\Areas;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/vehicle', name: 'app_api_vehicle_')]
#[OA\Tag(name: 'Vehicle')]
#[Areas(["default"])]
final class VehicleController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly VehicleRepository      $repository,
        private readonly EnergyRepository       $energyRepository,
        private readonly SerializerInterface    $serializer,
        private readonly ValidatorInterface     $validator,
    )
    {
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    #[OA\Post(
        path:"/api/vehicle/add",
        summary:"Ajout d'un nouveau véhicule",
        requestBody :new RequestBody(
            description: "Données du véhicule à ajouter",
            required: true,
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "brand",
                    type: "string",
                    example: "Renault"
                ),
                    new Property(
                        property: "model",
                        type: "string",
                        example: "R4"
                    ),
                    new Property(
                        property: "color",
                        type: "string",
                        example: "Blanche"
                    ),
                    new Property(
                        property: "licensePlate",
                        type: "string",
                        example: "9999 ZZ 75"
                    ),
                    new Property(
                        property: "licenseFirstDate",
                        type: "date",
                        example: "1970-01-01"
                    ),
                    new Property(
                        property: "nbPlace",
                        type: "integer",
                        example: 3
                    ),
                    new Property(
                        property: "energy",
                        type: "integer",
                        example: 1
                    ),
                ], type: "object"))]
        ),

    )]
    #[OA\Response(
        response: 201,
        description: 'Véhicule ajouté avec succès',
        content: new Model(type: Vehicle::class, groups: ['vehicle_read'])
    )]
    #[OA\Response(
        response: 400,
        description: 'Données invalides'
    )]
    #[OA\Response(
        response: 422, // Unprocessable Entity - pour les erreurs sémantiques comme "énergie non trouvée"
        description: 'Erreur sémantique dans les données (ex: énergie non trouvée)'
    )]
    public function add(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $rawContent = $request->getContent();

        try {
            // 1. Décoder le contenu JSON une seule fois
            $jsonData = json_decode($request->getContent(), true);

            // 2. Désérialiser l'objet Vehicle.
            $vehicle = $this->serializer->deserialize(
                $rawContent,
                Vehicle::class,
                'json'
            );
        } catch (NotEncodableValueException $e) {
            return new JsonResponse(['error' => true, 'message' => 'JSON malformé: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        // 3. Gérer l'énergie spécifiquement
        if (!isset($jsonData['energy']) || !is_numeric($jsonData['energy'])) {
            return new JsonResponse(['error' => true, 'message' => 'Le champ energy (ID) est manquant ou invalide.', 'field' => 'energy'], Response::HTTP_BAD_REQUEST);
        }

        $energy = $this->energyRepository->find($jsonData['energy']);
        if (!$energy) {
            // Utiliser HTTP 422 (Unprocessable Entity) est sémantiquement plus correct pour une ressource liée non trouvée
            return new JsonResponse(['error' => true, 'message' => 'Ce carburant n\'existe pas.', 'field' => 'energy'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $vehicle->setEnergy($energy);

        // 4. Définir les propriétés gérées par le serveur
        $vehicle->setOwner($user);
        $vehicle->setCreatedAt(new DateTimeImmutable());

        // 5. Validation de l'objet Vehicle complet (avec Energy, Owner, CreatedAt)
        $errors = $this->validator->validate($vehicle);
        if (count($errors) > 0) {
            // Formatter les erreurs pour une meilleure réponse API
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // 6. Persistance
        $this->manager->persist($vehicle);
        $this->manager->flush();

        // 7. Réponse
        return $this->json($vehicle, Response::HTTP_CREATED, [], ['groups' => 'vehicle_read']);

    }

    #[Route('/list', name: 'showAll', methods: 'GET')]
    #[Areas(["default"])]
    #[OA\Get(
        path:"/api/vehicle/list",
        summary:"Récupérer tous les véhicules du User.",
    )]
    #[OA\Response(
        response: 200,
        description: 'Véhicule(s) trouvée(s) avec succès',
        content: new Model(type: Vehicle::class, groups: ['vehicle_read'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Véhicule non trouvé'
    )]
    public function showAll(#[CurrentUser] ?User $user): JsonResponse
    {
        $vehicles = $this->repository->findBy(['owner' => $user->getId()]);

        return $this->json($vehicles, Response::HTTP_OK, [], ['groups' => 'vehicle_read']);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[Areas(["default"])]
    #[OA\Get(
        path:"/api/vehicle/{id}",
        summary:"Récupérer un véhicule du User avec son ID.",
    )]
    #[OA\Response(
        response: 200,
        description: 'Véhicule trouvé avec succès',
        content: new Model(type: Vehicle::class, groups: ['vehicle_read'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Véhicule non trouvé'
    )]
    public function showById(#[CurrentUser] ?User $user, int $id): JsonResponse
    {
        $vehicle = $this->repository->find($id);

        // Vérifier si le véhicule appartient à l'utilisateur connecté ou s'il existe, même message d'erreur
        if (!$vehicle || ($vehicle->getOwner()->getId() !== $user->getId())) {
            return $this->json(
                [
                    'error' => true,
                    'message' => 'Ce véhicule n\'existe pas'],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json($vehicle, Response::HTTP_OK, [], [
            'groups' => 'vehicle_read',
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[Areas(["default"])]
    #[OA\Put(
        path:"/api/vehicle/{id}",
        summary:"Modification d'un véhicule du User",
        requestBody :new RequestBody(
            description: "Données du véhicule à modifier.",
            required: true,
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "brand",
                    type: "string",
                    example: "Renault"
                ),
                    new Property(
                        property: "model",
                        type: "string",
                        example: "R4"
                    ),
                    new Property(
                        property: "color",
                        type: "string",
                        example: "Blanche"
                    ),
                    new Property(
                        property: "licensePlate",
                        type: "string",
                        example: "9999 ZZ 75"
                    ),
                    new Property(
                        property: "licenseFirstDate",
                        type: "date",
                        example: "1970-01-01"
                    ),
                    new Property(
                        property: "nbPlace",
                        type: "integer",
                        example: 3
                    ),
                    new Property(
                        property: "energy",
                        type: "integer",
                        example: 1
                    ),
                ], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Véhicule modifié avec succès',
        content: new Model(type: Vehicle::class, groups: ['vehicle_read'])
    )]
    #[OA\Response(
        response: 404,
        description: 'Véhicule non trouvé'
    )]
    public function edit(#[CurrentUser] ?User $user, int $id, Request $request): JsonResponse
    {
        $vehicle = $this->repository->findOneBy(['id' => $id, 'owner' => $user->getId()]);
        if (!$vehicle || ($vehicle->getOwner()->getId() !== $user->getId()))
        {
            return new JsonResponse(['error' => true, 'message' => 'Ce véhicule n\'existe pas.'], Response::HTTP_NOT_FOUND);
        }
        $vehicle = $this->serializer->deserialize(
            $request->getContent(),
            Vehicle::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $vehicle]
        );

        //Vérification sur les données
        $checkVehicleRequirements = $this->checkVehicleRequirements($vehicle, $request);
        if ($checkVehicleRequirements['error'] === true)
        {
            return new JsonResponse(
                [
                'error' => true,
                'message' => $checkVehicleRequirements['message'],
                'field' => $checkVehicleRequirements['field']
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        // Vérifier et associer l'énergie
        $energy = $this->energyRepository->find($data['energy']);
        if (!$energy) {
            return new JsonResponse(['error' => 'Ce carburant n\'existe pas', 'field' => 'energy'], Response::HTTP_BAD_REQUEST);
        }

        $vehicle->setEnergy($energy);

        $vehicle->setUpdatedAt(new DateTimeImmutable());

        $this->manager->flush();

        $responseData = $this->serializer->serialize($vehicle, 'json', ['groups' => ['vehicle_read']]);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[Areas(["default"])]
    #[OA\Delete(
        path:"/api/vehicle/{id}",
        summary:"Supprimer un véhicule du User.",
    )]
    #[OA\Response(
        response: 204,
        description: 'Véhicule supprimé avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Paramètre non trouvé'
    )]
    public function delete(#[CurrentUser] ?User $user, int $id): JsonResponse
    {
        $vehicle = $this->repository->findOneBy(['id' => $id, 'owner' => $user->getId()]);
        if ($vehicle) {
            $this->manager->remove($vehicle);
            $this->manager->flush();

            return new JsonResponse(['message' => 'Le véhicule a été supprimé avec succès.'], Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(['error' => true, 'message' => 'Ce véhicule n\'existe pas.'], Response::HTTP_NOT_FOUND);
    }

    private function checkVehicleRequirements(Vehicle $vehicle, Request $request): array
    {
        $isError = false;
        $returnMessage = 'ok';
        $returnField = '';

        // Vérifier que le nombre de places est au moins égal à 1.
        if ($vehicle->getNbPlace() < 1) {
            $isError = true;
            $returnMessage = 'Le nombre de places doit être au minimum de 1.';
            $returnField = 'nbPlace';
        }
        //La marque est obligatoire.
        if ($vehicle->getBrand() === null || $vehicle->getBrand() === '') {
            $isError = true;
            $returnMessage = 'La marque est obligatoire.';
            $returnField = 'brand';
        }
        //Le modèle est obligatoire.
        if ($vehicle->getModel() === null || $vehicle->getModel() === '') {
            $isError = true;
            $returnMessage = 'Le modèle est obligatoire.';
            $returnField = 'model';
        }
        //La couleur est obligatoire.
        if ($vehicle->getColor() === null || $vehicle->getColor() === '') {
            $isError = true;
            $returnMessage = 'La couleur est obligatoire.';
            $returnField = 'color';
        }
        //L'immatriculation est obligatoire et doit être unique
        if ($vehicle->getLicensePlate() === null || $vehicle->getLicensePlate() === '') {
            $isError = true;
            $returnMessage = 'L\'immatriculation est obligatoire.';
            $returnField = 'licensePlate';
        }
        //La date de première immatriculation est obligatoire et doit être au bon format.
        if ($vehicle->getLicenseFirstDate() === null ||
            $vehicle->getLicenseFirstDate() === '' ||
            (!($vehicle->getLicenseFirstDate() instanceof DateTime) &&
                !DateTime::createFromFormat('Y-m-d', $vehicle->getLicenseFirstDate()))) {
            $isError = true;
            $returnMessage = 'La date d\'immatriculation doit être une date valide au format YYYY-MM-DD.';
            $returnField = 'licenseFirstDate';
        }
        //L'énergie est obligatoire.
        $data = json_decode($request->getContent(), true);
        if (!isset($data['energy']) && $vehicle->getEnergy() === null) {
            $isError = true;
            $returnMessage = 'Il faut choisir une motorisation existante !';
            $returnField = 'energy';
        }


        return ['error' => $isError, 'message' => $returnMessage, 'field' => $returnField];
    }



}
