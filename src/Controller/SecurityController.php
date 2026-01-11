<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\User;
use App\Entity\UserRole;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('user_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
                if ($this->getUser()) {
            return $this->redirectToRoute('user_dashboard');
        }
        
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);
        $typ_konta = (string) $request->query->get('typ_konta', '');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['accountType'] === 'business_owner') {
                $businessFields = ['businessName', 'address', 'city', 'postalCode', 'formalBusinessName', 'businessPhone', 'businessEmail'];
                $missingFields = [];
                foreach ($businessFields as $field) {
                    if (empty($data[$field])) {
                        $missingFields[] = $field;
                    }
                }
                if (!empty($missingFields)) {
                    $this->addFlash('error', 'Wszystkie pola firmy są wymagane dla właścicieli firm.');
                    return $this->render('security/register.html.twig', [
                        'registrationForm' => $form->createView(),
                    ]);
                }
            }

            $user = new User();
            $user->setEmail($data['email']);
            $user->setName($data['name']);
            $user->setSurname($data['surname']);
            $user->setPhone($data['phone']);
            $user->setPasswordHash($this->passwordHasher->hashPassword($user, $data['password']));

            if ($data['accountType'] === 'business_owner') {
                $user->setRole(UserRole::BUSINESS_OWNER);

                $business = new Business();
                $business->setOwner($user);
                $business->setBusinessName($data['businessName']);
                $business->setAddress($data['address']);
                $business->setCity($data['city']);
                $business->setPostalCode($data['postalCode']);
                $business->setFormalBusinessName($data['formalBusinessName']);
                $business->setPhone($data['businessPhone']);
                $business->setEmail($data['businessEmail']);
                

                $this->entityManager->persist($business);
                error_log('Registered business_owner: ' . $user->getEmail());
            } else {
                $user->setRole(UserRole::USER);
                error_log('Registered user: ' . $user->getEmail());
            }

            $this->entityManager->persist($user);
            
            try {
                $this->entityManager->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $form->get('email')->addError(new \Symfony\Component\Form\FormError('Ten adres email jest już zarejestrowany. Użyj innego adresu.'));
                
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'typ_konta' => $typ_konta,
                ]);
            }

            $this->addFlash('success', 'Rejestracja zakończona pomyślnie! Możesz się teraz zalogować.');

            
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
            'typ_konta' => $typ_konta,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}