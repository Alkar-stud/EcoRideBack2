<?php

namespace App\Controller;

use App\Entity\Energy;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/energy', name: 'app_api_energy_')]
#[OA\Tag(name: 'Energy')]
final class EnergyController extends AbstractController{

    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly EnergyRepository       $repository,
        private readonly SerializerInterface    $serializer,
        private readonly ValidatorInterface     $validator
    )
    {
    }
    #[Route('/add', name: 'add', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[Areas(["ecoride"])]
    #[OA\Post(
        path:"/api/energy/add",
        summary:"Ajout d'un type d'énergie",
        requestBody :new RequestBody(
            description: "Données de l'énergie à ajouter'",
            required: true,
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "libelle",
                    type: "string",
                    example: "L'énergie du futur !"
                ),
                    new Property(
                        property: "isEco",
                        type: "boolean",
                        example: true
                    )], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 201,
        description: 'Énergie ajoutée avec succès',
        content: new Model(type: Energy::class)
    )]
    #[OA\Response(
        response: 400,
        description: 'Données invalides'
    )]
    public function add(Request $request): JsonResponse
    {
        /** @var Energy $energy */
        $energy = $this->serializer->deserialize($request->getContent(), Energy::class, 'json');
        $energy->setCreatedAt(new DateTimeImmutable());

        // Validation
        $errors = $this->validator->validate($energy);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->manager->persist($energy);
        $this->manager->flush();

        return new JsonResponse(
            [
                'id'  => $energy->getId(),
                'libelle'  => $energy->getLibelle(),
                'isEco' => $energy->isEco(),
                'createdAt' => $energy->getCreatedAt()
            ],
            Response::HTTP_CREATED
        );
    }
    #[Route('/list/', name: 'showAll', methods: 'GET')]
    #[Areas(["default"])]
    #[OA\Get(
        path:"/api/energy/list/",
        summary:"Récupérer toutes les énergies.",
    )]
    #[OA\Response(
        response: 200,
        description: 'Énergies trouvées avec succès',
        content: new Model(type: Energy::class)
    )]
    public function showAll(): JsonResponse
    {
        $energies = $this->repository->findBy([], ['isEco' => 'DESC', 'libelle' => 'ASC']);

        return $this->json($energies, Response::HTTP_OK, []);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[Areas(["default"])]
    #[OA\Get(
        path:"/api/energy/{id}",
        summary:"Récupérer une énergie à l'aide de son ID.",
    )]
    #[OA\Response(
        response: 200,
        description: 'Énergie trouvée avec succès',
        content: new Model(type: Energy::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Énergie non trouvée'
    )]
    public function showById(Energy $energy): JsonResponse
    {
        return $this->json($energy, Response::HTTP_OK, []);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[Areas(["ecoride"])]
    #[OA\Put(
        path:"/api/energy/{id}",
        summary:"Modification d'un type d'énergie",
        requestBody :new RequestBody(
            description: "Données du type d'énergie à modifier",
            required: true,
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "libelle",
                    type: "string",
                    example: "Le nouveau nom de l'énergie du futur !"
                ),
                    new Property(
                        property: "isEco",
                        type: "boolean",
                        example: true
                    )], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 404,
        description: 'Type d\'énergie non trouvé'
    )]
    #[OA\Response(
        response: 200,
        description: 'Type d\'énergie modifié avec succès'
    )]
    public function edit(Energy $existingEnergy, Request $request): JsonResponse
    {
        $this->serializer->deserialize(
            $request->getContent(),
            Energy::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $existingEnergy]
        );

        $existingEnergy->setUpdatedAt(new DateTimeImmutable());

        // Validation
        $errors = $this->validator->validate($existingEnergy);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->manager->flush();

        // Retourner l'entité mise à jour
        return $this->json($existingEnergy, Response::HTTP_OK, []);
    }


    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[Areas(["ecoride"])]
    #[OA\Delete(
        path:"/api/energy/{id}",
        summary:"Supprimer un type d'énergie.",
    )]
    #[OA\Response(
        response: 204,
        description: 'Type d\'énergie supprimé avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Type d\'énergie non trouvé'
    )]
    public function delete(Energy $energy): JsonResponse // Injection directe de l'entité
    {
        $this->manager->remove($energy);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
