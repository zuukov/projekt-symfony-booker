<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\Staff;
use App\Entity\UserRole;
use App\Form\BusinessFormType;
use App\Form\StaffFormType;
use App\Repository\BusinessRepository;
use App\Repository\StaffRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/owner')]
#[IsGranted('ROLE_BUSINESS_OWNER')]
class OwnerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BusinessRepository $businessRepository,
        private StaffRepository $staffRepository,
    ) {}

    #[Route('/', name: 'owner_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        error_log('Owner dashboard called for user: ' . $user->getEmail() . ' roles: ' . implode(', ', $user->getRoles()));

        // Check if user is business owner
        if ($user->getRole() !== UserRole::BUSINESS_OWNER) {
            throw $this->createAccessDeniedException('Access denied. Business owner role required.');
        }

        $businesses = $this->businessRepository->findBy(['owner' => $user]);

        return $this->render('owner/dashboard.html.twig', [
            'businesses' => $businesses,
        ]);
    }

    #[Route('/business/create', name: 'owner_business_create')]
    public function createBusiness(Request $request): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER) {
            throw $this->createAccessDeniedException('Access denied. Business owner role required.');
        }

        $business = new Business();
        $business->setOwner($user);

        $form = $this->createForm(BusinessFormType::class, $business, [
            'is_edit' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($business);
            $this->entityManager->flush();

            $this->addFlash('success', 'Business created successfully.');

            return $this->redirectToRoute('owner_business_staff', ['id' => $business->getId()]);
        }

        return $this->render('owner/business_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'is_edit' => false,
        ]);
    }

    #[Route('/business/{id}/edit', name: 'owner_business_edit')]
    public function editBusiness(Request $request, Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $form = $this->createForm(BusinessFormType::class, $business, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Business updated successfully.');

            return $this->redirectToRoute('owner_business_staff', ['id' => $business->getId()]);
        }

        return $this->render('owner/business_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'is_edit' => true,
        ]);
    }

    #[Route('/business/{id}/delete', name: 'owner_business_delete', methods: ['POST'])]
    public function deleteBusiness(Request $request, Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('delete'.$business->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($business);
            $this->entityManager->flush();

            $this->addFlash('success', 'Business deleted successfully.');
        }

        return $this->redirectToRoute('owner_dashboard');
    }

    #[Route('/business/{id}/staff', name: 'owner_business_staff')]
    public function listStaff(Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $staff = $this->staffRepository->findBy(['business' => $business]);

        return $this->render('owner/staff_list.html.twig', [
            'business' => $business,
            'staff' => $staff,
        ]);
    }

    #[Route('/business/{id}/staff/create', name: 'owner_staff_create')]
    public function createStaff(Request $request, Business $business): Response
    {
        $user = $this->getUser();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER || $business->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $staff = new Staff();
        $staff->setBusiness($business);

        $form = $this->createForm(StaffFormType::class, $staff);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($staff);
            $this->entityManager->flush();

            $this->addFlash('success', 'Staff member added successfully.');

            return $this->redirectToRoute('owner_business_staff', ['id' => $business->getId()]);
        }

        return $this->render('owner/staff_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'staff' => $staff,
            'is_edit' => false,
        ]);
    }

    #[Route('/business/{businessId}/staff/{staffId}/edit', name: 'owner_staff_edit')]
    public function editStaff(Request $request, int $businessId, Staff $staff): Response
    {
        $user = $this->getUser();
        $business = $staff->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER ||
            $business->getOwner() !== $user ||
            $business->getId() !== $businessId) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $form = $this->createForm(StaffFormType::class, $staff);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Staff member updated successfully.');

            return $this->redirectToRoute('owner_business_staff', ['id' => $business->getId()]);
        }

        return $this->render('owner/staff_form.html.twig', [
            'form' => $form->createView(),
            'business' => $business,
            'staff' => $staff,
            'is_edit' => true,
        ]);
    }

    #[Route('/business/{businessId}/staff/{staffId}/delete', name: 'owner_staff_delete', methods: ['POST'])]
    public function deleteStaff(Request $request, int $businessId, Staff $staff): Response
    {
        $user = $this->getUser();
        $business = $staff->getBusiness();

        if ($user->getRole() !== UserRole::BUSINESS_OWNER ||
            $business->getOwner() !== $user ||
            $business->getId() !== $businessId) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($this->isCsrfTokenValid('delete'.$staff->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($staff);
            $this->entityManager->flush();

            $this->addFlash('success', 'Staff member deleted successfully.');
        }

        return $this->redirectToRoute('owner_business_staff', ['id' => $business->getId()]);
    }
}
