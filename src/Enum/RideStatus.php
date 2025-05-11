<?php
// src/Enum/RideStatus.php

namespace App\Enum;

enum RideStatus: int
{
    case COMING = 1;
    case PROGRESSING = 2;
    case VALIDATIONPROCESSING = 3;
    case FINISHED = 4;
    case CANCELED = 5;
    case AWAITINGVALIDATION = 6;

    public function getLabel(): string
    {
        return match($this) {
            self::COMING => 'À Venir',
            self::PROGRESSING => 'En Cours',
            self::VALIDATIONPROCESSING => 'Approuvé',
            self::FINISHED => 'Terminé',
            self::CANCELED => 'Annulé',
            self::AWAITINGVALIDATION => 'En Attente De Validation'
        };
    }
}

/* Utilisation de l'enum dans un controller
<?php
// src/Controller/TripController.php

namespace App\Controller;

use App\Enum\RideStatus;
use App\Entity\Ride;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RideController extends AbstractController
{
    #[Route('/api/ride/approve/{id}', name: 'approve_ride', methods: ['POST'])]
    public function approveRide(Ride $ride): Response
    {
        // Utilisation de l'enum
        $ride->setStatus(RideStatus::APPROVED);

        // Traitement supplémentaire...

        return $this->json(['status' => 'success']);
    }

    #[Route('/api/ride/pending', name: 'list_pending_ride', methods: ['GET'])]
    public function listPendingRide(): Response
    {
        // Rechercher par valeur d'enum
        $pendingRide = $this->getDoctrine()->getRepository(Ride::class)
            ->findBy(['status' => RideStatus::PENDING]);

        return $this->json($pendingRide);
    }
}
*/

/* dans l'entité Ride
<?php
// src/Entity/Ride.php

namespace App\Entity;

use App\Enum\RideStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Ride
{
    // ...

    #[ORM\Column(type: 'integer')]
    private RideStatus $status;

    public function __construct()
    {
        // Valeur par défaut
        $this->status = RideStatus::PENDING;
    }

    public function getStatus(): RideStatus
    {
        return $this->status;
    }

    public function setStatus(RideStatus $status): self
    {
        $this->status = $status;
        return $this;
    }
}


*/
