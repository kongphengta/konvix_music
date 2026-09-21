<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

final class UnverifiedUserAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly RouterInterface $router,
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        $email = trim((string) $request->request->get('_username', ''));

        if ('' !== $email && str_contains($exception->getMessage(), "Votre compte n'est pas encore vérifié")) {
            $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user && !$user->isVerified()) {
                return new RedirectResponse($this->router->generate('app_verify_email', [
                    'email' => $email,
                    'from_login' => 1,
                ]));
            }
        }

        return new RedirectResponse($this->router->generate('app_login'));
    }
}
