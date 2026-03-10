<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class ReviewController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/reviews', name: 'reviews_create', methods: ['POST'])]
    #[IsGranted('ROLE_CUSTOMER')]
    public function createReview(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['product_id']) || !isset($data['rating'])) {
            return new JsonResponse(['error' => 'Product ID and rating are required'], Response::HTTP_BAD_REQUEST);
        }

        $rating = (int)$data['rating'];
        if ($rating < 1 || $rating > 5) {
            return new JsonResponse(['error' => 'Rating must be between 1 and 5'], Response::HTTP_BAD_REQUEST);
        }

        $product = $this->em->getRepository(Product::class)->find($data['product_id']);
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        /** @var Customer $customer */
        $customer = $this->getUser();
        if (!$customer instanceof Customer) {
            return new JsonResponse(['error' => 'Invalid user'], Response::HTTP_FORBIDDEN);
        }

        // Check if customer already reviewed this product
        $existingReview = $this->em->getRepository(Review::class)->findOneBy([
            'customer' => $customer,
            'product' => $product,
        ]);

        if ($existingReview) {
            return new JsonResponse(['error' => 'You have already reviewed this product'], Response::HTTP_CONFLICT);
        }

        $review = new Review();
        $review->setProduct($product);
        $review->setCustomer($customer);
        $review->setRating($rating);
        $review->setComment($data['comment'] ?? null);

        $this->em->persist($review);
        $this->em->flush();

        return new JsonResponse([
            'message' => 'Review created successfully',
            'review' => [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'product_id' => $product->getId(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/reviews', name: 'reviews_list', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER')]
    public function listCustomerReviews(): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $this->getUser();
        if (!$customer instanceof Customer) {
            return new JsonResponse(['error' => 'Invalid user'], Response::HTTP_FORBIDDEN);
        }

        $reviews = $customer->getReviews();

        $data = [];
        foreach ($reviews as $review) {
            $data[] = [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'created_at' => $review->getCreatedAt()->format('Y-m-d H:i:s'),
                'product' => [
                    'id' => $review->getProduct()->getId(),
                    'name' => $review->getProduct()->getName(),
                    'imageUrl' => $review->getProduct()->getImageUrl(),
                ],
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/reviews/{id}', name: 'reviews_update', methods: ['PUT'])]
    #[IsGranted('ROLE_CUSTOMER')]
    public function updateReview(int $id, Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $this->getUser();
        if (!$customer instanceof Customer) {
            return new JsonResponse(['error' => 'Invalid user'], Response::HTTP_FORBIDDEN);
        }

        $review = $this->em->getRepository(Review::class)->find($id);
        if (!$review) {
            return new JsonResponse(['error' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        if ($review->getCustomer()->getId() !== $customer->getId()) {
            return new JsonResponse(['error' => 'You can only update your own reviews'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['rating'])) {
            $rating = (int)$data['rating'];
            if ($rating < 1 || $rating > 5) {
                return new JsonResponse(['error' => 'Rating must be between 1 and 5'], Response::HTTP_BAD_REQUEST);
            }
            $review->setRating($rating);
        }

        if (isset($data['comment'])) {
            $review->setComment($data['comment']);
        }

        $this->em->flush();

        return new JsonResponse([
            'message' => 'Review updated successfully',
            'review' => [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
            ]
        ]);
    }

    #[Route('/reviews/{id}', name: 'reviews_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_CUSTOMER')]
    public function deleteReview(int $id): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $this->getUser();
        if (!$customer instanceof Customer) {
            return new JsonResponse(['error' => 'Invalid user'], Response::HTTP_FORBIDDEN);
        }

        $review = $this->em->getRepository(Review::class)->find($id);
        if (!$review) {
            return new JsonResponse(['error' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        if ($review->getCustomer()->getId() !== $customer->getId()) {
            return new JsonResponse(['error' => 'You can only delete your own reviews'], Response::HTTP_FORBIDDEN);
        }

        $this->em->remove($review);
        $this->em->flush();

        return new JsonResponse(['message' => 'Review deleted successfully']);
    }

    #[Route('/products/{id}/reviews', name: 'product_reviews_list', methods: ['GET'])]
    public function listProductReviews(int $id): JsonResponse
    {
        $product = $this->em->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $reviews = $product->getReviews();

        $data = [];
        foreach ($reviews as $review) {
            $data[] = [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'created_at' => $review->getCreatedAt()->format('Y-m-d H:i:s'),
                'customer' => [
                    'id' => $review->getCustomer()->getId(),
                    'name' => $review->getCustomer()->getName(),
                ],
            ];
        }

        return new JsonResponse($data);
    }
}
