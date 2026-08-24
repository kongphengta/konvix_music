<?php

namespace App\Controller;

use App\Entity\ArtistProfile;
use App\Entity\User;
use App\Form\ArtistProfileFormType;
use App\Form\ArtistRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/register/artist')]
class ArtistRegistrationController extends AbstractController
{
    #[Route('', name: 'app_register_artist')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user          = new User();
        $artistProfile = new ArtistProfile();

        $accountForm = $this->createForm(ArtistRegistrationFormType::class, $user);
        $profileForm = $this->createForm(ArtistProfileFormType::class, $artistProfile);

        $accountForm->handleRequest($request);
        $profileForm->handleRequest($request);

        if ($accountForm->isSubmitted() && $accountForm->isValid()
            && $profileForm->isSubmitted() && $profileForm->isValid()
        ) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $accountForm->get('plainPassword')->getData())
            );
            $user->setRoles(['ROLE_ARTIST']);
            $user->setSubscriptionPlan('free');

            $slug = strtolower($slugger->slug($artistProfile->getStageName())->toString());
            $artistProfile->setSlug($slug);
            $artistProfile->setUser($user);

            $em->persist($user);
            $em->persist($artistProfile);
            $em->flush();

            $this->addFlash('success', 'Votre compte artiste a été créé ! Notre équipe le validera sous 24h.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/artist.html.twig', [
            'accountForm' => $accountForm,
            'profileForm' => $profileForm,
        ]);
    }
}
