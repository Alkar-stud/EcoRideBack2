<?php

namespace App\Service;

use App\Repository\EcoRideRepository;

class NoticeService
{
    private EcoRideRepository $ecoRideRepository;

    public function __construct(EcoRideRepository $ecoRideRepository)
    {
        $this->ecoRideRepository = $ecoRideRepository;
    }

    public function getDefaultStatus(): ?string
    {
        $ecoRide = $this->ecoRideRepository->findOneBy(['libelle' => 'DEFAULT_NOTICE_STATUS_ID']);
        // Vérification si l'entité existe et récupération de la valeur des paramètres
        return $ecoRide ? $ecoRide->getParameters() : null;
    }
}