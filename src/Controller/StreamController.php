<?php

namespace App\Controller;

use App\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StreamController extends AbstractController
{
    #[Route('/stream/{slug}', name: 'app_stream_song')]
    #[IsGranted('ROLE_USER')]
    public function play(string $slug, SongRepository $songRepository): Response
    {
        $song = $songRepository->findOneBy(['slug' => $slug]);

        if (!$song || !$song->getFilename()) {
            throw $this->createNotFoundException('Fichier audio introuvable.');
        }

        $filePath = $this->getParameter('kernel.project_dir')
            . '/public/uploads/songs/'
            . $song->getFilename();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier audio introuvable.');
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'audio/mpeg');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);
        // Empêcher la mise en cache du fichier audio protégé
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
