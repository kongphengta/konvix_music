<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class ArtistAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();

        if ($user instanceof User) {
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                return new RedirectResponse($this->router->generate('admin'));
            }

            if (in_array('ROLE_ARTISTE', $user->getRoles(), true)) {
                return new RedirectResponse($this->router->generate('app_artist_dashboard'));
            }
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }
}
