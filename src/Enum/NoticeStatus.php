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

use App\Enum\RideStatus;
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
        $notice->setStatus(RideStatus::APPROVED);

        // Traitement supplémentaire...

        return $this->json(['status' => 'success']);
    }

    #[Route('/api/notices/pending', name: 'list_pending_notices', methods: ['GET'])]
    public function listPendingNotices(): Response
    {
        // Rechercher par valeur d'enum
        $pendingNotices = $this->getDoctrine()->getRepository(Notice::class)
            ->findBy(['status' => RideStatus::PENDING]);

        return $this->json($pendingNotices);
    }
}
*/

/* dans l'entité Notice
<?php
// src/Entity/Ride.php

namespace App\Entity;

use App\Enum\RideStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Notice
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
