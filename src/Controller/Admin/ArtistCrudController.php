<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Form\ArtistType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArtistCrudController extends AbstractController
{
    #[Route('/admin/artists', name: 'app_admin_artists_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $artists = $doctrine->getRepository(Artist::class)->findBy([], ['id' => 'DESC']);

        return $this->render('admin/artists/index.html.twig', [
            'artists' => $artists,
        ]);
    }

    #[Route('/admin/artists/new', name: 'app_admin_artists_new')]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($artist);
            $entityManager->flush();

            $this->addFlash('success', 'Artiste ajouté avec succès.');

            return $this->redirectToRoute('app_admin_artists_index');
        }

        return $this->render('admin/artists/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/artists/{id}/edit', name: 'app_admin_artists_edit')]
    public function edit(Artist $artist, Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();

            $this->addFlash('success', 'Artiste mis à jour.');

            return $this->redirectToRoute('app_admin_artists_index');
        }

        return $this->render('admin/artists/edit.html.twig', [
            'form' => $form,
            'artist' => $artist,
        ]);
    }

    #[Route('/admin/artists/{id}/delete', name: 'app_admin_artists_delete', methods: ['POST'])]
    public function delete(Artist $artist, Request $request, ManagerRegistry $doctrine): Response
    {
        if ($this->isCsrfTokenValid('delete' . $artist->getId(), $request->request->get('_token'))) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($artist);
            $entityManager->flush();
            $this->addFlash('success', 'Artiste supprimé.');
        }

        return $this->redirectToRoute('app_admin_artists_index');
    }
}
