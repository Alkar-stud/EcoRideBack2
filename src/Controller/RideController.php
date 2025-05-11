<?php

namespace App\Controller;

use App\Entity\Ride;
use App\Entity\User;
use App\Repository\RideRepository;
use App\Service\MailService;
use App\Service\RideMongoService;
use App\Service\RideService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Areas;
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
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/ride', name: 'app_api_ride_')]
#[OA\Tag(name: 'Ride')]
#[Areas(["default"])]
final class RideController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface  $manager,
        private readonly RideRepository          $repository,
        private readonly SerializerInterface     $serializer,
        private readonly RideService             $rideService,
        private readonly RideMongoService        $rideMongoService,
        private readonly MailService             $mailService,
    )
    {
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    #[OA\Post(
        path:"/api/ride/add",
        summary:"Ajout d'un nouveau covoiturage",
        requestBody :new RequestBody(
            description: "Données du statut du covoiturage. duration est le temps de trajet en minutes",
            required: true,
            content: [new MediaType(mediaType:"application/json",
                schema: new Schema(properties: [new Property(
                    property: "startingAddress",
                    type: "string",
                    example: "rue|VILLE"
                ),
                    new Property(
                        property: "arrivalAddress",
                        type: "string",
                        example: "rue|VILLE"
                    ),
                    new Property(
                        property: "startingAt",
                        type: "datetime",
                        example: "2025-07-01 10:00:00"
                    ),
                    new Property(
                        property: "duration",
                        type: "integer",
                        example: 120
                    ),
                    new Property(
                        property: "cost",
                        type: "integer",
                        example: 15
                    ),
                    new Property(
                        property: "maxNbPlaces",
                        type: "integer",
                        example: 3
                    ),
                    new Property(
                        property: "vehicle",
                        type: "integer",
                        example: 3
                    ),
                ], type: "object"))]
        ),
    )]
    #[OA\Response(
        response: 201,
        description: 'Covoiturage ajouté avec succès',
        content: new Model(type: Ride::class, groups: ['ride_read'])
    )]
    public function add(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $trip = $this->serializer->deserialize($request->getContent(), Ride::class, 'json');



        return new JsonResponse('ok', Response::HTTP_OK, [], true);
    }



}
