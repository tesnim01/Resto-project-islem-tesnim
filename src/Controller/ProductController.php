<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/public/products', name: 'products_list', methods: ['GET'])]
    public function listProducts(Request $request): JsonResponse
    {
        $categoryId = $request->query->get('category');
        $available = $request->query->get('available');

        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        if ($categoryId) {
            $qb->where('p.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($available !== null) {
            $qb->andWhere('p.available = :available')
                ->setParameter('available', filter_var($available, FILTER_VALIDATE_BOOLEAN));
        }

        $products = $qb->getQuery()->getResult();

        $data = array_map(function (Product $product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'imageUrl' => $product->getImageUrl(),
                'available' => $product->isAvailable(),
                'category' => [
                    'id' => $product->getCategory()?->getId(),
                    'name' => $product->getCategory()?->getName(),
                ],
            ];
        }, $products);

        return new JsonResponse($data);
    }

    #[Route('/public/products/{id}', name: 'product_show', methods: ['GET'])]
    public function showProduct(int $id): JsonResponse
    {
        $product = $this->em->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $ingredients = [];
        foreach ($product->getIngredients() as $ingredient) {
            $ingredients[] = [
                'id' => $ingredient->getId(),
                'name' => $ingredient->getName(),
            ];
        }

        $reviews = [];
        foreach ($product->getReviews() as $review) {
            $reviews[] = [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'createdAt' => $review->getCreatedAt()->format('Y-m-d H:i:s'),
                'customer' => [
                    'id' => $review->getCustomer()?->getId(),
                    'name' => $review->getCustomer()?->getName(),
                ],
            ];
        }

        return new JsonResponse([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'imageUrl' => $product->getImageUrl(),
            'available' => $product->isAvailable(),
            'category' => [
                'id' => $product->getCategory()?->getId(),
                'name' => $product->getCategory()?->getName(),
            ],
            'ingredients' => $ingredients,
            'reviews' => $reviews,
        ]);
    }

    #[Route('/admin/products', name: 'admin_products_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createProduct(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['price']) || !isset($data['category_id'])) {
            return new JsonResponse(['error' => 'Name, price, and category_id are required'], Response::HTTP_BAD_REQUEST);
        }

        $category = $this->em->getRepository(Category::class)->find($data['category_id']);
        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description'] ?? null);
        $product->setPrice($data['price']);
        $product->setImageUrl($data['imageUrl'] ?? null);
        $product->setAvailable($data['available'] ?? true);
        $product->setCategory($category);

        $this->em->persist($product);
        $this->em->flush();

        return new JsonResponse([
            'message' => 'Product created successfully',
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/admin/products/{id}', name: 'admin_products_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateProduct(int $id, Request $request): JsonResponse
    {
        $product = $this->em->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }
        if (isset($data['imageUrl'])) {
            $product->setImageUrl($data['imageUrl']);
        }
        if (isset($data['available'])) {
            $product->setAvailable($data['available']);
        }
        if (isset($data['category_id'])) {
            $category = $this->em->getRepository(Category::class)->find($data['category_id']);
            if ($category) {
                $product->setCategory($category);
            }
        }

        $this->em->flush();

        return new JsonResponse([
            'message' => 'Product updated successfully',
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ]
        ]);
    }

    #[Route('/admin/products/{id}', name: 'admin_products_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteProduct(int $id): JsonResponse
    {
        $product = $this->em->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($product);
        $this->em->flush();

        return new JsonResponse(['message' => 'Product deleted successfully']);
    }
}
