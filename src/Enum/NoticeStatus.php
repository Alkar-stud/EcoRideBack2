<?php
// src/Enum/NoticeStatus.php

namespace App\Enum;

enum NoticeStatus: int
{
    case FILED = 1;
    case INVALIDATIONPROCESS = 2;
    case APPROVED = 3;
    case REJECTED = 4;

    public function getLabel(): string
    {
        return match($this) {
            self::FILED => 'Déposé',
            self::INVALIDATIONPROCESS => 'En Cours De Validation',
            self::APPROVED => 'Validé',
            self::REJECTED => 'Refusé'
        };
    }
}

/* Utilisation de l'enum dans un controller ==> à adpater à notice
<?php
// src/Controller/NoticeController.php

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
