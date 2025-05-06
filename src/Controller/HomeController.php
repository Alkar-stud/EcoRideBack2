<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return new Response('La doc de l\'API est <a href="/api/doc/">ici</a>', Response::HTTP_OK);
    }
}
