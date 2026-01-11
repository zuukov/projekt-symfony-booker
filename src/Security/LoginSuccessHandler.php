<?php

namespace App\Security;

use App\Entity\UserRole;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();

        error_log('LoginSuccessHandler called for user: ' . $user->getEmail() . ' role: ' . $user->getRole()->value);
        
        if ($user->getRole() === UserRole::BUSINESS_OWNER) {
            
            error_log('Redirecting to owner_dashboard');
            return new RedirectResponse($this->urlGenerator->generate('owner_dashboard'));
        } elseif ($user->getRole() === UserRole::USER) {
            error_log('Redirecting to user_dashboard');
            return new RedirectResponse($this->urlGenerator->generate('user_dashboard'));
        }

        return new RedirectResponse($this->urlGenerator->generate('user_dashboard'));
    }
}