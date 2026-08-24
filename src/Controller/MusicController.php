<?php

namespace App\Controller;

use App\Entity\Song;
use App\Form\SongType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MusicController extends AbstractController
{
    #[Route('/admin/song/new', name: 'app_song_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $song = new Song();
        $form = $this->createForm(SongType::class, $song);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération du fichier MP3
            $mp3File = $form->get('mp3File')->getData();

            if ($mp3File) {
                $newFilename = uniqid() . '.' . $mp3File->guessExtension();

                $mp3File->move(
                    $this->getParameter('mp3_directory'),
                    $newFilename
                );

                $song->setFilename($newFilename);
            }

            // Enregistrement en base
            $em->persist($song);
            $em->flush();

            $this->addFlash('success', 'La chanson a été ajoutée avec succès.');

            return $this->redirectToRoute('app_song_list');
        }

        return $this->render('music/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
