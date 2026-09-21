<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ArtistValidationController extends AbstractController
{
    #[Route('/admin/artists/validation', name: 'app_admin_artist_validation')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $artists = $doctrine->getRepository(Artist::class)->findBy(['isPublished' => false], ['id' => 'DESC']);

        return $this->render('admin/artists/validation.html.twig', [
            'artists' => $artists,
        ]);
    }

    #[Route('/admin/artists/{id}/publish', name: 'app_admin_artist_publish', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function publish(Artist $artist, Request $request, ManagerRegistry $doctrine, MailerInterface $mailer): Response
    {
        if ($this->isCsrfTokenValid('publish' . $artist->getId(), $request->request->get('_token'))) {
            $artist->setIsPublished(true);
            $doctrine->getManager()->flush();

            $user = $artist->getUser();
            if ($user && $user->getEmail()) {
                $message = (new Email())
                    ->from(new Address('noreply@konvix.music', 'Konvix Music'))
                    ->to(new Address($user->getEmail()))
                    ->subject('Félicitations ! Votre profil artiste est validé')
                    ->text(sprintf(
                        "Bonjour %s,\n\nFélicitations ! Votre profil artiste a été validé sur Konvix Music. Vous pouvez maintenant publier vos morceaux, partager votre univers et faire grandir votre audience.\n\nMerci pour votre engagement, et bonne création !\n",
                        $user->getFirstName() ?: 'artiste'
                    ));

                $mailer->send($message);
            }

            $this->addFlash('success', 'Votre profil artiste est validé ! Vous pouvez maintenant publier vos morceaux.');
        }

        return $this->redirectToRoute('app_admin_artist_validation');
    }

    #[Route('/admin/artists/{id}/reject', name: 'app_admin_artist_reject', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function reject(Artist $artist, Request $request, ManagerRegistry $doctrine): Response
    {
        if ($this->isCsrfTokenValid('reject' . $artist->getId(), $request->request->get('_token'))) {
            $artist->setIsPublished(false);
            $doctrine->getManager()->flush();
            $this->addFlash('warning', 'Le profil artiste a été refusé et reste non publié.');
        }

        return $this->redirectToRoute('app_admin_artist_validation');
    }
}
