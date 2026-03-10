<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/', name: 'app_')]
class AuthController extends AbstractController
{
    #[Route('', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        if ($this->getUser()) {
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                return $this->redirectToRoute('app_dashboard_admin');
            }
            return $this->redirectToRoute('app_dashboard_customer');
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Check if user is already logged in (session-based auth)
        $user = $this->getUser();
        if ($user) {
            // Redirect based on role
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('app_dashboard_admin');
            }
            return $this->redirectToRoute('app_dashboard_customer');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('register', name: 'register', methods: ['GET'])]
    public function registerForm(): Response
    {
        // Check if user is already logged in (session-based auth)
        $user = $this->getUser();
        if ($user) {
            // Redirect based on role
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('app_dashboard_admin');
            }
            return $this->redirectToRoute('app_dashboard_customer');
        }

        return $this->render('auth/register.html.twig');
    }

    #[Route('logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
