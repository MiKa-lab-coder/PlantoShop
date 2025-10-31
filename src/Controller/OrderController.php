<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/api/orders', name: 'api_orders_list', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $orders = $orderRepository->findAll();
        return $this->json($orders, Response::HTTP_OK, [], ['groups' => 'order:read']);
    }

    #[Route('/api/orders/{id}', name: 'api_orders_show', methods: ['GET'])]
    public function show(Order $order): JsonResponse
    {
        if ($order->getClient() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You cannot view this order.');
        }
        return $this->json($order, Response::HTTP_OK, [], ['groups' => 'order:read']);
    }

    #[Route('/api/orders', name: 'api_orders_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $cart = $user->getCart();

        if (!$cart || $cart->getPlants()->isEmpty()) {
            return $this->json(['error' => 'Your cart is empty.'], Response::HTTP_BAD_REQUEST);
        }

        $order = new Order();
        $orderDetails = new OrderDetails();

        // 1. Remplir OrderDetails avec une "photographie" des données
        $orderDetails->setClientFirstName($user->getFirstName());
        $orderDetails->setClientLastName($user->getLastName());
        $orderDetails->setClientEmail($user->getEmail());
        $orderDetails->setClientAddress($user->getAddress());
        $orderDetails->setClientPhoneNumber($user->getPhoneNumber());

        $totalPrice = 0;
        $plantSummary = [];
        foreach ($cart->getPlants() as $plant) {
            $totalPrice += $plant->getPrice();
            $plantSummary[] = [
                'id' => $plant->getId(),
                'name' => $plant->getName(),
                'price' => $plant->getPrice(),
            ];
        }
        $orderDetails->setTotalPrice($totalPrice / 100); // Supposant que le prix est en centimes
        $orderDetails->setPlantSummary($plantSummary);

        // 2. Lier les entités
        $order->setClient($user);
        $order->setCart($cart);
        $order->setOrderDetails($orderDetails);
        $orderDetails->setOrderRef($order);

        // 3. Dissocier le panier de l'utilisateur pour qu'il puisse en créer un nouveau
        $user->setCart(null);

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json($order, Response::HTTP_CREATED, [], ['groups' => 'order:read']);
    }

    #[Route('/api/orders/{id}', name: 'api_orders_delete', methods: ['DELETE'])]
    public function destroy(Order $order, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($order);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
