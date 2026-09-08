<?php

namespace App\Controller;

use App\Repository\SongRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StreamController extends AbstractController
{
    private const LISTEN_TRACKING_WINDOW = 1800;

    #[Route('/stream/{slug}', name: 'app_stream_song')]
    #[IsGranted('ROLE_USER')]
    public function play(
        string $slug,
        SongRepository $songRepository,
        ?RequestStack $requestStack = null,
        ?EntityManagerInterface $entityManager = null
    ): Response {
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

        $this->registerListen($song, $requestStack, $entityManager);

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'audio/mpeg');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }

    private function registerListen(
        \App\Entity\Song $song,
        ?RequestStack $requestStack,
        ?EntityManagerInterface $entityManager
    ): void {
        if (!$requestStack || !$entityManager) {
            return;
        }

        $session = $requestStack->getSession();

        if (!$session) {
            return;
        }

        $songId = (string) ($song->getId() ?? $song->getSlug() ?? 'unknown');
        $sessionKey = 'song_listen_' . $songId;
        $now = time();
        $lastListen = (int) $session->get($sessionKey, 0);

        if ($lastListen > 0 && ($now - $lastListen) < self::LISTEN_TRACKING_WINDOW) {
            return;
        }

        $song->setListenCount($song->getListenCount() + 1);
        $session->set($sessionKey, $now);
        $entityManager->flush();
    }
}
