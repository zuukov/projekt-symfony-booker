<?php

namespace App\Controller;

use App\Entity\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    #[Route('/dashboard', name: 'user_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        
        if ($user->getRole() !== UserRole::USER) {
            throw $this->createAccessDeniedException('Access denied. User role required.');
        }

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
        ]);
    }
}