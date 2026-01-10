<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserController extends AbstractController
{
    #[Route('/dashboard', name: 'user_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->getRole() === UserRole::BUSINESS_OWNER || $this->isGranted('ROLE_BUSINESS_OWNER')) {
            return $this->redirectToRoute('owner_dashboard');
        }

        if ($user->getRole() !== UserRole::USER && !$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_login');
        }

        $userData = [
            'fullName' => trim(($user->getName() ?? '') . ' ' . ($user->getSurname() ?? '')) ?: 'Użytkownik',
            'email' => $user->getEmail(),
            'phone' => $user->getPhone() ?: '',
            'avatarUrl' => 'https://media.istockphoto.com/id/1337144146/vector/default-avatar-profile-icon-vector.jpg?s=612x612&w=0&k=20&c=BIbFwuv7FxTWvh5S3vB6bkT0Qv8Vn8N5Ffseq84ClGI=',
        ];

        $appointments = [
            [
                'statusLabel' => 'Zakończona',
                'statusTone' => 'success', // success|warning|danger
                'serviceName' => 'Strzyżenie krótkich włosów i brody',
                'business' => [
                    'name' => 'Gentlemen Barber Shop Poznań',
                    'avatarUrl' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=200&q=60',
                    'url' => '/business/gentlemen-barber-poznan',
                ],
                'date' => [
                    'monthShort' => 'Październik',
                    'day' => 15,
                    'year' => 2025,
                    'time' => '10:45',
                    'iso' => '2025-10-15 10:45',
                ],
                'ctaLabel' => 'Oceń wizytę',
                'ctaUrl' => '#',
            ],
            [
                'statusLabel' => 'Zakończona',
                'statusTone' => 'success',
                'serviceName' => 'Strzyżenie krótkich włosów i brody',
                'business' => [
                    'name' => 'Gentlemen Barber Shop Poznań',
                    'avatarUrl' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=200&q=60',
                    'url' => '/business/gentlemen-barber-poznan',
                ],
                'date' => [
                    'monthShort' => 'Kwiecień',
                    'day' => 14,
                    'year' => 2025,
                    'time' => '11:45',
                    'iso' => '2025-04-14 11:45',
                ],
                'ctaLabel' => 'Oceń wizytę',
                'ctaUrl' => '#',
            ],
        ];

        $paymentMethods = [
            [
                'brand' => 'Visa',
                'last4' => '4242',
                'exp' => '12/27',
                'isDefault' => true,
            ],
            [
                'brand' => 'Mastercard',
                'last4' => '4444',
                'exp' => '09/26',
                'isDefault' => false,
            ],
        ];

        $paymentHistory = [
            [
                'businessName' => 'Gentlemen Barber Shop Poznań',
                'amount' => 90.00,
                'currency' => 'PLN',
                'paidAt' => '2025-10-15 10:45',
                'status' => 'Opłacona',
            ],
            [
                'businessName' => 'Salon Kosmetyczny Bella',
                'amount' => 150.00,
                'currency' => 'PLN',
                'paidAt' => '2025-09-02 18:10',
                'status' => 'Opłacona',
            ],
        ];

        $reviews = [
            [
                'businessName' => 'Gentlemen Barber Shop Poznań',
                'businessUrl' => '/business/gentlemen-barber-poznan#reviews',
                'rating' => 5,
                'createdAt' => '2025-10-15',
                'text' => 'Super atmosfera i pełen profesjonalizm. Polecam!',
            ],
            [
                'businessName' => 'Salon Kosmetyczny Bella',
                'businessUrl' => '/business/salon-kosmetyczny-bella#reviews',
                'rating' => 4,
                'createdAt' => '2025-09-02',
                'text' => 'Bardzo miło, czysto i sprawnie — wrócę na pewno.',
            ],
        ];

        $tabs = [
            ['key' => 'appointments', 'label' => 'Wizyty', 'icon' => 'fa-regular fa-calendar-check'],
            ['key' => 'payments', 'label' => 'Płatności', 'icon' => 'fa-regular fa-credit-card'],
            ['key' => 'reviews', 'label' => 'Opinie', 'icon' => 'fa-regular fa-star'],
            ['key' => 'settings', 'label' => 'Ustawienia konta', 'icon' => 'fa-regular fa-user']
        ];

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'userData' => $userData,
            'tabs' => $tabs,
            'defaultTab' => 'appointments',
            'appointments' => $appointments,
            'paymentMethods' => $paymentMethods,
            'paymentHistory' => $paymentHistory,
            'reviews' => $reviews,
        ]);
    }
}
