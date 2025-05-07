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

#[Route('/api/energy', name: 'app_api_energy_')]
#[OA\Tag(name: 'Energy')]
final class EnergyController extends AbstractController{

    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly EnergyRepository       $repository,
        private readonly SerializerInterface    $serializer,
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
        description: 'Préférence ajoutée avec succès',
        content: new Model(type: Energy::class)
    )]
    public function add(Request $request): JsonResponse
    {
        $energy = $this->serializer->deserialize($request->getContent(), Energy::class, 'json');
        $energy->setCreatedAt(new DateTimeImmutable());

        // Convertir le champ "libelle" pour qu'il ait une seule majuscule au premier caractère, même avec des accents
        $libelle = $energy->getLibelle();
        $formattedLibelle = mb_convert_case($libelle, MB_CASE_TITLE, "UTF-8");
        $energy->setLibelle($formattedLibelle);

        $this->manager->persist($energy);
        $this->manager->flush();

        return new JsonResponse(
            [
                'id'  => $energy->getId(),
                'libelle'  => $energy->getLibelle(),
                'isEco' => $energy->isEco(),
                'createdAt' => $energy->getCreatedAt()
            ],
            Response::HTTP_OK
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

        if ($energies) {
            $responseData = $this->serializer->serialize(
                $energies,
                'json'
            );

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
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
    public function showById(int $id): JsonResponse
    {
        $energy = $this->repository->findOneBy(['id' => $id]);
        if ($energy) {
            $responseData = $this->serializer->serialize($energy, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
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
        description: 'Paramètre non trouvé'
    )]
    #[OA\Response(
        response: 200,
        description: 'Paramètre modifié avec succès',
        content: new Model(type: Energy::class)
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $energy = $this->repository->findOneBy(['id' => $id]);

        if ($energy) {
            $energy = $this->serializer->deserialize(
                $request->getContent(),
                Energy::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $energy]
            );
            // Convertir le champ "libelle" pour qu'il ait une seule majuscule au premier caractère, même avec des accents
            $libelle = $energy->getLibelle();
            $formattedLibelle = mb_convert_case($libelle, MB_CASE_TITLE, "UTF-8");
            $energy->setLibelle($formattedLibelle);
            $energy->setUpdatedAt(new DateTimeImmutable());

            $this->manager->flush();

            return new JsonResponse(
                [
                    'id'  => $energy->getId(),
                    'libelle'  => $energy->getLibelle(),
                    'isEco' => $energy->isEco(),
                    'createdAt' => $energy->getCreatedAt(),
                    'updateAt' => $energy->getUpdatedAt()
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
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
        description: 'Paramètre non trouvé'
    )]
    public function delete(int $id): JsonResponse
    {
        $energy = $this->repository->findOneBy(['id' => $id]);
        if ($energy) {
            $this->manager->remove($energy);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);

    }
}
