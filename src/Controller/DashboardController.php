<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/dashboard', name: 'api_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/customer', name: 'customer', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER')]
    public function customerDashboard(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'message' => 'Welcome to Customer Dashboard',
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => 'ROLE_CUSTOMER',
            ],
            'features' => [
                'View menu and products',
                'Place orders',
                'Write reviews',
                'View order history',
            ]
        ]);
    }

    #[Route('/admin', name: 'admin', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'message' => 'Welcome to Admin Dashboard',
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => 'ROLE_ADMIN',
            ],
            'features' => [
                'Manage products',
                'Manage categories',
                'Manage orders',
                'Manage users',
                'View statistics',
            ]
        ]);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function getCurrentUser(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt() ? $user->getCreatedAt()->format('Y-m-d H:i:s') : null,
        ]);
    }

    #[Route('/me', name: 'update_me', methods: ['PUT', 'PATCH'])]
    public function updateCurrentUser(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);

        // Update name if provided
        if (isset($data['name']) && !empty($data['name'])) {
            $user->setName($data['name']);
        }

        // Update phone if provided
        if (isset($data['phone'])) {
            $user->setPhone($data['phone'] ?: null);
        }

        try {
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Profile updated successfully',
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'roles' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt() ? $user->getCreatedAt()->format('Y-m-d H:i:s') : null,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to update profile: ' . $e->getMessage()], 400);
        }
    }
}
