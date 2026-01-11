<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\BookingStatus;
use App\Entity\Cards;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/uzytkownik')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserController extends AbstractController
{
    private LoggerInterface $logger;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(LoggerInterface $logger, UserPasswordHasherInterface $passwordHasher)
    {
        $this->logger = $logger;
        $this->passwordHasher = $passwordHasher;
    }
    #[Route('/moje-konto', name: 'user_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
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

        
        $bookingRepo = $entityManager->getRepository(Booking::class);
        $allBookings = $bookingRepo->findBy(['user' => $user], ['startsAt' => 'DESC']);

        $upcomingAppointments = [];
        $pastAppointments = [];

        foreach ($allBookings as $booking) {
            $statusLabel = match ($booking->getStatus()) {
                BookingStatus::CONFIRMED => 'Potwierdzona',
                BookingStatus::PENDING => 'Oczekująca',
                BookingStatus::CANCELLED => 'Anulowana',
                BookingStatus::COMPLETED => 'Zakończona',
            };
            $statusTone = match ($booking->getStatus()) {
                BookingStatus::CONFIRMED => 'success',
                BookingStatus::PENDING => 'warning',
                BookingStatus::CANCELLED => 'danger',
                BookingStatus::COMPLETED => 'success',
            };

            $appointmentData = [
                'statusLabel' => $statusLabel,
                'statusTone' => $statusTone,
                'serviceName' => $booking->getService()->getName(),
                'business' => [
                    'name' => $booking->getBusiness()->getBusinessName(),
                    'avatarUrl' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=200&q=60',
                    'url' => '/firma/' . $booking->getBusiness()->getId(),
                ],
                'date' => [
                    'monthShort' => $booking->getStartsAt()->format('M'),
                    'day' => $booking->getStartsAt()->format('d'),
                    'year' => $booking->getStartsAt()->format('Y'),
                    'time' => $booking->getStartsAt()->format('H:i'),
                    'iso' => $booking->getStartsAt()->format('Y-m-d H:i'),
                ],
                'ctaLabel' => 'Zarządzaj wizytą',
                'ctaUrl' => $this->generateUrl('user_bookings'),
            ];

            if ($booking->getStatus() === BookingStatus::PENDING || $booking->getStatus() === BookingStatus::CONFIRMED) {
                $upcomingAppointments[] = $appointmentData;
            }
            elseif ($booking->getStatus() === BookingStatus::COMPLETED) {
                $pastAppointments[] = $appointmentData;
            }
        }

        $paymentMethods = [];
        $cards = $entityManager->getRepository(Cards::class)->findBy(['user_id' => $user->getId()], ['is_default' => 'DESC', 'id' => 'ASC']);
        foreach ($cards as $card) {
            $paymentMethods[] = [
                'id' => $card->getId(),
                'brand' => $this->getCardBrand($card->getCardNumber()),
                'last4' => substr($card->getCardNumber(), -4),
                'exp' => $card->getValidUntil(),
                'isDefault' => $card->isDefault(),
            ];
        }

        $paymentHistory = [];
        $completedBookings = $entityManager->getRepository(Booking::class)->findCompletedByUser($user);
        foreach ($completedBookings as $booking) {
            $paidAt = $booking->getEndsAt() ?: $booking->getStartsAt();
            $paymentHistory[] = [
                'businessName' => $booking->getBusiness()->getBusinessName(),
                'serviceName' => $booking->getService()->getName(),
                'amount' => (float) $booking->getPriceAtBooking(),
                'currency' => 'PLN',
                'paidAt' => $paidAt?->format('Y-m-d H:i') ?? '',
                'status' => 'Opłacona',
            ];
        }

        $reviews = [];
        $reviewRepo = $entityManager->getRepository(Review::class);
        foreach ($completedBookings as $booking) {
            
            $existingReview = $reviewRepo->findOneBy([
                'booking' => $booking,
            ]);

            $reviews[] = [
                'businessName' => $booking->getBusiness()->getBusinessName(),
                'businessUrl' => '/firma/' . $booking->getBusiness()->getId() . '#reviews',
                'serviceName' => $booking->getService()->getName(),
                'bookingDate' => ($booking->getEndsAt() ?: $booking->getStartsAt())?->format('Y-m-d H:i'),
                'bookingId' => $booking->getId(),
                'hasReview' => (bool)$existingReview,
                'rating' => $existingReview?->getRating(),
                'text' => $existingReview?->getComment(),
                'createdAt' => $existingReview?->getCreatedAt()?->format('Y-m-d'),
            ];
        }

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
            'upcomingAppointments' => $upcomingAppointments,
            'pastAppointments' => $pastAppointments,
            'paymentMethods' => $paymentMethods,
            'paymentHistory' => $paymentHistory,
            'reviews' => $reviews,
        ]);
    }

    private function getCardBrand(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        if (preg_match('/^4/', $cardNumber)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'Mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'American Express';
        } elseif (preg_match('/^6(?:011|5)/', $cardNumber)) {
            return 'Discover';
        } else {
            return 'Unknown';
        }
    }

    #[Route('/dodaj-karte', name: 'user_add_card', methods: ['POST'])]
    public function addCard(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->logger->info('addCard called');

        if (!$this->isCsrfTokenValid('authenticate', $request->headers->get('X-CSRF-Token'))) {
            $this->logger->error('Invalid CSRF token');
            return new JsonResponse(['success' => false, 'message' => 'Nieprawidłowy token CSRF.']);
        }

        $user = $this->getUser();
        if (!$user) {
            $this->logger->error('User not logged in');
            return new JsonResponse(['success' => false, 'message' => 'Użytkownik nie jest zalogowany.']);
        }

        $this->logger->info('User: ' . $user->getId());

        $cardNumber = $request->request->get('card_number');
        $validUntil = $request->request->get('valid_until');
        $cvcCode = $request->request->get('cvc_code');
        $country = $request->request->get('country');

        $this->logger->info('Data: ' . $cardNumber . ' ' . $validUntil . ' ' . $cvcCode . ' ' . $country);

        
        if (strlen($cardNumber) !== 16 || !is_numeric($cardNumber)) {
            $this->logger->error('Invalid card number');
            return new JsonResponse(['success' => false, 'message' => 'Numer karty musi mieć 16 cyfr.']);
        }
        if (!preg_match('/^\d{2}\/\d{2}$/', $validUntil)) {
            $this->logger->error('Invalid valid until');
            return new JsonResponse(['success' => false, 'message' => 'Data ważności musi być w formacie MM/RR.']);
        }
        if (strlen($cvcCode) !== 3 || !is_numeric($cvcCode)) {
            $this->logger->error('Invalid CVC');
            return new JsonResponse(['success' => false, 'message' => 'Kod CVC musi mieć 3 cyfry.']);
        }
        if (empty($country)) {
            $this->logger->error('Empty country');
            return new JsonResponse(['success' => false, 'message' => 'Kraj jest wymagany.']);
        }

        $existingCards = $entityManager->getRepository(Cards::class)->findBy(['user_id' => $user->getId()]);
        $isFirstCard = empty($existingCards);

        $card = new Cards();
        $card->setUserId($user->getId());
        $card->setCardNumber($cardNumber);
        $card->setValidUntil($validUntil);
        $card->setCvcCode((int)$cvcCode);
        $card->setCountry($country);
        $card->setIsDefault($isFirstCard); 

        try {
            $entityManager->persist($card);
            $entityManager->flush();
            $this->logger->info('Card saved successfully');
        } catch (\Exception $e) {
            $this->logger->error('Save error: ' . $e->getMessage());
            return new JsonResponse(['success' => false, 'message' => 'Błąd zapisu: ' . $e->getMessage()]);
        }

        return new JsonResponse(['success' => true, 'message' => 'Karta została dodana.']);
    }

    #[Route('/zapisz-ustawienia', name: 'user_update_settings', methods: ['POST'])]
    public function updateSettings(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('authenticate', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Nieprawidłowy token CSRF.']);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Użytkownik nie jest zalogowany.']);
        }

        $fullName = $request->request->get('fullName');
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');

        
        if (empty($fullName)) {
            return new JsonResponse(['success' => false, 'message' => 'Imię i nazwisko jest wymagane.']);
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['success' => false, 'message' => 'Nieprawidłowy adres e-mail.']);
        }

        
        $nameParts = explode(' ', trim($fullName), 2);
        $name = $nameParts[0] ?? '';
        $surname = $nameParts[1] ?? '';

        $user->setName($name);
        $user->setSurname($surname);
        $user->setEmail($email);
        $user->setPhone($phone);

        
        $currentPassword = $request->request->get('currentPassword');
        $newPassword = $request->request->get('newPassword');
        $newPasswordRepeat = $request->request->get('newPasswordRepeat');

        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                return new JsonResponse(['success' => false, 'message' => 'Aktualne hasło jest wymagane do zmiany hasła.']);
            }
            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                return new JsonResponse(['success' => false, 'message' => 'Aktualne hasło jest nieprawidłowe.']);
            }
            if ($newPassword !== $newPasswordRepeat) {
                return new JsonResponse(['success' => false, 'message' => 'Nowe hasła nie są identyczne.']);
            }
            if (strlen($newPassword) < 6) {
                return new JsonResponse(['success' => false, 'message' => 'Nowe hasło musi mieć co najmniej 6 znaków.']);
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
            $user->setPasswordHash($hashedPassword);
        }

        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Błąd zapisu: ' . $e->getMessage()]);
        }

        return new JsonResponse(['success' => true, 'message' => 'Ustawienia zostały zapisane.']);
    }

    #[Route('/ustaw-domyslna-karte/{id}', name: 'user_set_default_card', methods: ['POST'])]
    public function setDefaultCard(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('authenticate', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Nieprawidłowy token CSRF.']);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Użytkownik nie jest zalogowany.']);
        }

        $card = $entityManager->getRepository(Cards::class)->find($id);
        if (!$card || $card->getUserId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Karta nie została znaleziona.']);
        }

        
        $entityManager->createQuery('UPDATE App\Entity\Cards c SET c.is_default = false WHERE c.user_id = :userId')
            ->setParameter('userId', $user->getId())
            ->execute();

       
        $card->setIsDefault(true);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Karta została ustawiona jako domyślna.']);
    }

    #[Route('/dodaj-opinie', name: 'user_add_review', methods: ['POST'])]
    public function addReview(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('authenticate', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Nieprawidłowy token CSRF.']);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Użytkownik nie jest zalogowany.']);
        }

        $bookingId = $request->request->get('booking_id');
        $rating = (int) $request->request->get('rating');
        $comment = $request->request->get('comment');

        if ($rating < 1 || $rating > 5) {
            return new JsonResponse(['success' => false, 'message' => 'Ocena musi być w zakresie 1-5.']);
        }

        $booking = $entityManager->getRepository(Booking::class)->find($bookingId);
        if (!$booking || $booking->getUser()?->getId() !== $user->getId() || $booking->getStatus() !== BookingStatus::COMPLETED) {
            return new JsonResponse(['success' => false, 'message' => 'Nie można ocenić tej wizyty.']);
        }

        $reviewRepo = $entityManager->getRepository(Review::class);
        $review = $reviewRepo->findOneBy([
            'booking' => $booking,
        ]);

        if (!$review) {
            $review = new Review();
            $review->setUser($user);
            $review->setBusiness($booking->getBusiness());
            $review->setBooking($booking);
        }

        $review->setRating($rating);
        $review->setComment($comment);
        $review->setCreatedAt(new \DateTime());

        try {
            $entityManager->persist($review);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Błąd zapisu opinii: ' . $e->getMessage()]);
        }

        return new JsonResponse(['success' => true, 'message' => 'Dziękujemy za opinię!']);
    }

    #[Route('/usun-karte/{id}', name: 'user_delete_card', methods: ['POST'])]
    public function deleteCard(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('authenticate', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Nieprawidłowy token CSRF.']);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Użytkownik nie jest zalogowany.']);
        }

        $card = $entityManager->getRepository(Cards::class)->find($id);
        if (!$card || $card->getUserId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Karta nie została znaleziona.']);
        }

        $wasDefault = $card->isDefault();
        $entityManager->remove($card);
        $entityManager->flush();

        
        if ($wasDefault) {
            $otherCards = $entityManager->getRepository(Cards::class)
                ->findBy(['user_id' => $user->getId()], ['id' => 'ASC']);
            if (!empty($otherCards)) {
                $otherCards[0]->setIsDefault(true);
                $entityManager->flush();
            }
        }

        return new JsonResponse(['success' => true, 'message' => 'Karta została usunięta.']);
    }
}
