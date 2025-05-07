<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ImageController extends AbstractController
{
    #[Route('/images/{filename}', name: 'serve_image')]
    public function serveImage(string $filename): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/' . $filename;

        if (!file_exists($path)) {
            throw $this->createNotFoundException('Image non trouvée');
        }

        $response = new BinaryFileResponse($path);
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
