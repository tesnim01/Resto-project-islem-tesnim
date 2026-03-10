<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/public/categories', name: 'categories_list', methods: ['GET'])]
    public function listCategories(): JsonResponse
    {
        $categories = $this->em->getRepository(Category::class)->findAll();

        $data = array_map(function (Category $category) {
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
                'productCount' => $category->getProducts()->count(),
            ];
        }, $categories);

        return new JsonResponse($data);
    }

    #[Route('/admin/categories', name: 'admin_categories_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createCategory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return new JsonResponse(['error' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }

        $category = new Category();
        $category->setName($data['name']);
        $category->setDescription($data['description'] ?? null);

        $this->em->persist($category);
        $this->em->flush();

        return new JsonResponse([
            'message' => 'Category created successfully',
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/admin/categories/{id}', name: 'admin_categories_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateCategory(int $id, Request $request): JsonResponse
    {
        $category = $this->em->getRepository(Category::class)->find($id);

        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $category->setName($data['name']);
        }
        if (isset($data['description'])) {
            $category->setDescription($data['description']);
        }

        $this->em->flush();

        return new JsonResponse([
            'message' => 'Category updated successfully',
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ]
        ]);
    }

    #[Route('/admin/categories/{id}', name: 'admin_categories_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteCategory(int $id): JsonResponse
    {
        $category = $this->em->getRepository(Category::class)->find($id);

        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($category);
        $this->em->flush();

        return new JsonResponse(['message' => 'Category deleted successfully']);
    }
}
