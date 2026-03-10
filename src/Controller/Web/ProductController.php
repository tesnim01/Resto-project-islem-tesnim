<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products', name: 'app_')]
class ProductController extends AbstractController
{
    #[Route('', name: 'products', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('products/index.html.twig', [
            'categoryId' => $request->query->get('category'),
        ]);
    }
}
