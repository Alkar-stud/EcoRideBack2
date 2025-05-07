<?php

namespace App\Service;

use App\Entity\TripStatus;
use App\Entity\Vehicle;
use App\Repository\EcoRideRepository;
use App\Repository\TripRepository;
use App\Repository\TripStatusRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;


class TripService
{
    private EcoRideRepository $ecoRideRepository;
    private VehicleRepository $vehicleRepository;
    private TripStatusRepository $tripStatusRepository;
    private TripRepository $tripRepository;

    public function __construct(
        private readonly EntityManagerInterface  $manager,
        EcoRideRepository $ecoRideRepository,
        VehicleRepository $vehicleRepository,
        TripStatusRepository $tripStatusRepository,
        TripRepository $tripRepository,
    )
    {
        $this->ecoRideRepository = $ecoRideRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->tripStatusRepository = $tripStatusRepository;
        $this->tripRepository = $tripRepository;
    }

    public function getDefaultStatus():  ?TripStatus
    {
        $ecoRide = $this->ecoRideRepository->findOneBy(['libelle' => 'DEFAULT_TRIP_STATUS_ID']);

        if (!$ecoRide) {
            return null;
        }

        $statusId = $ecoRide->getParameters();
        $statusRepository = $this->manager->getRepository(TripStatus::class);

        return $statusRepository->find($statusId);
    }

    public function getFinishedStatus():  ?TripStatus
    {
        $ecoRide = $this->ecoRideRepository->findOneBy(['libelle' => 'FINISHED_TRIP_STATUS_ID']);

        if (!$ecoRide) {
            return null;
        }

        $statusId = $ecoRide->getParameters();
        $statusRepository = $this->manager->getRepository(TripStatus::class);

        return $statusRepository->find($statusId);
    }

    public function getTripVehicle($vehicleId, $user): ?Vehicle
    {
        $vehicle = $this->vehicleRepository->findOneBy(['id' => $vehicleId]);

        if (!$vehicle) {
            return null;
        }
        //On vérifie si le véhicule appartient bien à CurrentUser
        if ($vehicle->getOwner()->getId() !== $user->getId()) {
            return null;
        }

        return $vehicle;

    }

    public function getPossibleStatus(): array
    {
        $statuses = $this->tripStatusRepository->findAll();
        $result['all'] = 'all';
        foreach (($statuses) as $status) {
            $result[$status->getCode()] = $status->getId();
        }
        return $result;
    }

    public function getPossibleActions(): array
    {
        return [
            "update" => ["initial" => ["coming"], "become" => "coming"],
            "start" => ["initial" => ["coming"], "become" => "progressing"],
            "stop" => ["initial" => ["progressing"], "become" => "validationProcess"],
            "badxp" => ["initial" => ["validationProcess"], "become" => "awaitingValidation"],
            "finish" => ["initial" => ["awaitingValidation", "validationProcess"], "become" => "finished"],
            "cancel" => ["initial" => ["coming"], "become" => "canceled"]
        ];
    }


    //Valide si l'action existe et est possible.
    private function validateEditRequest($action, array $possibleActions): bool
    {
        if (!isset($action) || !array_key_exists($action, $possibleActions)) {
            return false;
        }
        return true;
    }

    public function isActionPossible($action, $id, $user): array
    {
        //action possible selon l'état du covoiturage avec l'état suivant selon l'action demandée
        $possibleActions = $this->getPossibleActions();

        //Vérification si l'action demandée est possible
        $requestIsValide = $this->validateEditRequest($action, $possibleActions);
        if (!$requestIsValide)
        {
            return ['error' => 'unknown_action', "message" => 'Cette action est impossible'];
        }

        //Récupération de l'entité
        $covoiturage = $this->tripRepository->findOneBy(['id' => $id, 'owner' => $user->getId()]);
        //Si le covoiturage n'existe pas
        if (!$covoiturage) {
            return ['error' => 'unknown_covoiturage', "message" => 'Ce covoiturage n\'existe pas'];
        }
        //Si user n'est pas owner
        if ($covoiturage->getOwner() !== $user) {
            return ['error' => 'owner', "message" => 'Ce covoiturage n\'existe pas dans vos covoiturages'];
        }
        //Si l'état initial ne le permet pas
        if (!in_array($covoiturage->getStatus()->getCode(), $possibleActions[$action]["initial"]))
        {
            //Définition des réponses en fonction de l'état
            $returnMessage = match ($action) {
                'start' => 'Le covoiturage ne peut pas être démarré.',
                'stop' => 'Le covoiturage ne peut pas être arrêté.',
                'cancel' => 'Le covoiturage ne peut pas être annulé.',
                'badxp' => 'Le covoiturage ne peut pas être soumis au contrôle de la plateforme.',
                'finish' => 'Le covoiturage ne peut pas être clôturé.',
                default => 'Cette action est impossible dans cet état.',
            };
            return [
                'error' => 'initial_status',
                "message" => $returnMessage
            ];
        }

        return ['error' => 'ok', 'become' => $possibleActions[$action]["become"]];
    }


}