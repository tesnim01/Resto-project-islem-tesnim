<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/orders', name: 'orders_create', methods: ['POST'])]
    #[IsGranted('ROLE_CUSTOMER')]
    public function createOrder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
            return new JsonResponse(['error' => 'Order items are required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var Customer $customer */
        $customer = $this->getUser();
        if (!$customer instanceof Customer) {
            return new JsonResponse(['error' => 'Invalid user'], Response::HTTP_FORBIDDEN);
        }

        $order = new Order();
        $order->setCustomer($customer);
        $order->setStatus(OrderStatus::PENDING);

        foreach ($data['items'] as $itemData) {
            if (!isset($itemData['product_id']) || !isset($itemData['quantity'])) {
                continue;
            }

            $product = $this->em->getRepository(Product::class)->find($itemData['product_id']);
            if (!$product || !$product->isAvailable()) {
                continue;
            }

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity((int)$itemData['quantity']);
            $orderItem->setUnitPrice($product->getPrice());
            $order->addOrderItem($orderItem);
        }

        if ($order->getOrderItems()->isEmpty()) {
            return new JsonResponse(['error' => 'No valid items in order'], Response::HTTP_BAD_REQUEST);
        }

        $order->calculateTotal();

        $this->em->persist($order);
        $this->em->flush();

        $items = [];
        foreach ($order->getOrderItems() as $orderItem) {
            $items[] = [
                'product_id' => $orderItem->getProduct()->getId(),
                'product_name' => $orderItem->getProduct()->getName(),
                'quantity' => $orderItem->getQuantity(),
                'unit_price' => $orderItem->getUnitPrice(),
            ];
        }

        return new JsonResponse([
            'message' => 'Order created successfully',
            'order' => [
                'id' => $order->getId(),
                'status' => $order->getStatus()->value,
                'total_price' => $order->getTotalPrice(),
                'order_date' => $order->getOrderDate()->format('Y-m-d'),
                'items' => $items,
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/orders', name: 'orders_list', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER')]
    public function listOrders(): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $this->getUser();
        if (!$customer instanceof Customer) {
            return new JsonResponse(['error' => 'Invalid user'], Response::HTTP_FORBIDDEN);
        }

        $orders = $customer->getOrders();

        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->getId(),
                'status' => $order->getStatus()->value,
                'total_price' => $order->getTotalPrice(),
                'order_date' => $order->getOrderDate()->format('Y-m-d'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/admin/orders', name: 'admin_orders_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listAllOrders(): JsonResponse
    {
        $orders = $this->em->getRepository(Order::class)->findAll();

        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->getId(),
                'status' => $order->getStatus()->value,
                'total_price' => $order->getTotalPrice(),
                'order_date' => $order->getOrderDate()->format('Y-m-d'),
                'customer' => [
                    'id' => $order->getCustomer()->getId(),
                    'name' => $order->getCustomer()->getName(),
                    'email' => $order->getCustomer()->getEmail(),
                ],
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/admin/orders/{id}/status', name: 'admin_orders_update_status', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateOrderStatus(int $id, Request $request): JsonResponse
    {
        $order = $this->em->getRepository(Order::class)->find($id);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['status'])) {
            return new JsonResponse(['error' => 'Status is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $status = OrderStatus::from($data['status']);
            $order->setStatus($status);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Invalid status'], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return new JsonResponse([
            'message' => 'Order status updated successfully',
            'order' => [
                'id' => $order->getId(),
                'status' => $order->getStatus()->value,
            ]
        ]);
    }
}
