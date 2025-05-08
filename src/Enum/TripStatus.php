<?php
// src/Enum/TripStatus.php

namespace App\Enum;

enum TripStatus: int
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

use App\Enum\TripStatus;
use App\Entity\Notice;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NoticeController extends AbstractController
{
    #[Route('/api/notices/approve/{id}', name: 'approve_notice', methods: ['POST'])]
    public function approveNotice(Notice $notice): Response
    {
        // Utilisation de l'enum
        $notice->setStatus(TripStatus::APPROVED);

        // Traitement supplémentaire...

        return $this->json(['status' => 'success']);
    }

    #[Route('/api/notices/pending', name: 'list_pending_notices', methods: ['GET'])]
    public function listPendingNotices(): Response
    {
        // Rechercher par valeur d'enum
        $pendingNotices = $this->getDoctrine()->getRepository(Notice::class)
            ->findBy(['status' => TripStatus::PENDING]);

        return $this->json($pendingNotices);
    }
}
*/

/* dans l'entité Notice
<?php
// src/Entity/Trip.php

namespace App\Entity;

use App\Enum\TripStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Notice
{
    // ...

    #[ORM\Column(type: 'integer')]
    private TripStatus $status;

    public function __construct()
    {
        // Valeur par défaut
        $this->status = TripStatus::PENDING;
    }

    public function getStatus(): TripStatus
    {
        return $this->status;
    }

    public function setStatus(TripStatus $status): self
    {
        $this->status = $status;
        return $this;
    }
}


*/
