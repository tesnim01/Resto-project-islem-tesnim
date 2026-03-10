<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    #[Route('/products', name: 'products', methods: ['GET'])]
    public function manageProducts(): Response
    {
        return $this->render('admin/products.html.twig');
    }

    #[Route('/categories', name: 'categories', methods: ['GET'])]
    public function manageCategories(): Response
    {
        return $this->render('admin/categories.html.twig');
    }

    #[Route('/orders', name: 'orders', methods: ['GET'])]
    public function manageOrders(): Response
    {
        return $this->render('admin/orders.html.twig');
    }

    #[Route('/users', name: 'users', methods: ['GET'])]
    public function manageUsers(): Response
    {
        return $this->render('admin/users.html.twig');
    }
}
