<?php

namespace App\Controller;

use App\Entity\Preferences;
use App\Entity\User;
use App\Repository\PreferencesRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Areas;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/account/preferences', name: 'app_api_account_preferences_')]
#[OA\Tag(name: 'User/Preferences')]
#[Areas(["default"])]
final class PreferencesController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly PreferencesRepository  $repository,
        private readonly SerializerInterface    $serializer
    )
    {
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    #[OA\Post(
        path:"/api/account/preferences/add",
        summary:"Ajout d'une préférence à l'utilisateur connecté",
        requestBody :new RequestBody(
            description: "Données de la préférence à inscrire",
            required: true,
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "libelle",
                    type: "string",
                    example: "Ma préférence"
                ),
                    new Property(
                        property: "description",
                        type: "text",
                        example: "J'écoute du métal"
                    )], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 201,
        description: 'Préférence ajoutée avec succès',
        content: new Model(type: User::class, groups: ['preferences_user'])
    )]
    public function add(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $preferences = $this->serializer->deserialize($request->getContent(), Preferences::class, 'json');
        $preferences->setCreatedAt(new DateTimeImmutable());
        $preferences->setUserPreferences($user);
        $preferences->setLibelle($preferences->getLibelle());

        $this->manager->persist($preferences);
        $this->manager->flush();

        return new JsonResponse(
            [
                'id'  => $preferences->getId(),
                'libelle'  => $preferences->getLibelle(),
                'description' => $preferences->getDescription(),
                'createdAt' => $preferences->getCreatedAt(),
                'userId' => $preferences->getUserPreferences()->getId()
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/list/', name: 'showAll', methods: 'GET')]
    #[OA\Get(
        path:"/api/account/preferences/list/",
        summary:"Récupérer toutes les préférences du User connecté",
    )]
    #[OA\Response(
        response: 200,
        description: 'Préférences trouvées avec succès',
        content: new Model(type: Preferences::class, groups: ['preferences_user'])
    )]
    public function showAll(#[CurrentUser] ?User $user): JsonResponse
    {
        $preferences = $this->repository->findBy(['userPreferences' => $user->getId()]);

        if ($preferences) {
            $responseData = $this->serializer->serialize(
                $preferences,
                'json',
                ['groups' => ['preferences_user']]
            );

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path:"/api/account/preferences/{id}",
        summary:"Récupérer une préférence du User connecté",
    )]
    #[OA\Response(
        response: 404,
        description: 'Préférence non trouvée'
    )]
    #[OA\Response(
        response: 200,
        description: 'Préférence trouvée avec succès',
        content: new Model(type: Preferences::class, groups: ['preferences_user'])
    )]
    public function showById(#[CurrentUser] ?User $user, int $id): JsonResponse
    {

        $preferences = $this->repository->findOneBy(['id' => $id, 'userPreferences' => $user->getId()]);
        if ($preferences) {
            $responseData = $this->serializer->serialize($preferences, 'json', ['groups' => ['preferences_user']]);

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(['error' => true, 'message' => 'Cette préférence n\'existe pas.'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path:"/api/account/preferences/{id}",
        summary:"Modifier une préférence du User connecté",
        requestBody :new RequestBody(
            description: "Données de l'utilisateur à modifier",
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "libelle",
                    type: "string",
                    example: "Nouveau libellé"
                ),
                    new Property(
                        property: "description",
                        type: "string",
                        example: "Nouvelle description"
                    ),
                ], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 404,
        description: 'Préférence non trouvée'
    )]
    #[OA\Response(
        response: 200,
        description: 'Préférence modifiée avec succès',
        content: new Model(type: Preferences::class, groups: ['preferences_user'])
    )]
    public function edit(#[CurrentUser] ?User $user, int $id, Request $request): JsonResponse
    {
        $preferences = $this->repository->findOneBy(['id' => $id , 'userPreferences' => $user->getId()]);

        if ($preferences) {
            $preferences = $this->serializer->deserialize(
                $request->getContent(),
                Preferences::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $preferences]
            );
            $preferences->setUpdatedAt(new DateTimeImmutable());


            $this->manager->flush();

            $responseData = $this->serializer->serialize($preferences, 'json', ['groups' => ['preferences_user']]);

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(['error' => true, 'message' => 'Cette préférence n\'existe pas.'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path:"/api/account/preferences/{id}",
        summary:"Supprimer une préférence",
    )]
    #[OA\Response(
        response: 204,
        description: 'Préférence supprimée avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Préférence non trouvée'
    )]
    public function delete(#[CurrentUser] ?User $user, int $id): JsonResponse
    {
        $preferences = $this->repository->findOneBy(['id' => $id, 'userPreferences' => $user->getId()]);
        if ($preferences) {
            //On ne supprime pas smokingAllowed et petsAllowed
            if ($preferences->getLibelle() === 'smokingAllowed' || $preferences->getLibelle() === 'petsAllowed') {
                return new JsonResponse(['error' => true, 'message' => 'Cette préférence ne peut pas être supprimée.'], Response::HTTP_BAD_REQUEST);
            }
            $this->manager->remove($preferences);
            $this->manager->flush();

            return new JsonResponse(['message' => 'Cette préférence a été supprimée avec succès.'], Response::HTTP_OK);
        }

        return new JsonResponse(['error' => true, 'message' => 'Cette préférence n\'existe pas.'], Response::HTTP_NOT_FOUND);
    }
}