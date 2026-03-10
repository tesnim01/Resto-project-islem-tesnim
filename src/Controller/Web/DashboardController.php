<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard', name: 'app_dashboard_')]
class DashboardController extends AbstractController
{
    #[Route('/customer', name: 'customer', methods: ['GET'])]
    public function customerDashboard(Request $request): Response
    {
        // For now, allow access - authentication will be handled client-side
        // JWT token is in localStorage and checked by JavaScript
        // In production, you'd want to validate JWT server-side
        
        // Try to get user from session (form_login) or allow access if not set
        // Client-side JavaScript will validate JWT and redirect if needed
        $user = $this->getUser();
        
        return $this->render('dashboard/customer.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin', name: 'admin', methods: ['GET'])]
    public function adminDashboard(Request $request): Response
    {
        // For now, allow access - authentication will be handled client-side
        // Client-side JavaScript will validate JWT and redirect if needed
        $user = $this->getUser();
        
        return $this->render('dashboard/admin.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        
        // Client-side will check JWT token
        return $this->render('dashboard/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
