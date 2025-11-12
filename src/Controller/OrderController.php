<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\OrderRepository;
use App\Repository\PlantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    // Route pour que l'admin voie toutes les commandes
    #[Route('/api/orders', name: 'api_orders_list_all', methods: ['GET'])]
    public function listAllOrders(OrderRepository $orderRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $orders = $orderRepository->findAll();
        return $this->json($orders, Response::HTTP_OK, [], ['groups' => 'order:read']);
    }

    // Route pour qu'un utilisateur voie son propre historique de commandes
    #[Route('/api/user/orders', name: 'api_user_orders_list', methods: ['GET'])]
    public function listUserOrders(): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        return $this->json($user->getOrders(), Response::HTTP_OK, [], ['groups' => 'order:read']);
    }

    // Route pour qu'un utilisateur (ou admin) voie une commande spécifique
    #[Route('/api/orders/{id}', name: 'api_orders_show', methods: ['GET'])]
    public function show(Order $order): JsonResponse
    {
        if ($order->getClient() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas voir cette commande.');
        }
        return $this->json($order, Response::HTTP_OK, [], ['groups' => 'order:read']);
    }

    // Route pour créer une commande à partir d'un panier envoyé par le front-end
    #[Route('/api/orders', name: 'api_orders_create', methods: ['POST'])]
    public function create(Request $request, PlantRepository $plantRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $cartItems = $data['items'] ?? [];

        if (empty($cartItems)) {
            return $this->json(['error' => 'Le panier est vide.'], Response::HTTP_BAD_REQUEST);
        }

        $order = new Order();
        $order->setClient($user);

        $orderDetails = new OrderDetails();
        $orderDetails->setTheOrder($order);
        $orderDetails->setClientFirstName($user->getFirstName());
        $orderDetails->setClientLastName($user->getLastName());
        $orderDetails->setClientEmail($user->getEmail());
        $orderDetails->setClientAddress($user->getAddress());
        $orderDetails->setClientPhoneNumber($user->getPhoneNumber());

        $totalPrice = 0;
        $plantSummary = [];

        foreach ($cartItems as $item) {
            $plant = $plantRepository->find($item['plant']['id']);
            if (!$plant) {
                return $this->json(['error' => 'Plante non trouvée: ' . $item['plant']['name']], Response::HTTP_NOT_FOUND);
            }

            $quantity = $item['quantity'];
            $totalPrice += $plant->getPrice() * $quantity;
            
            $order->addPlant($plant);
            $plantSummary[] = [
                'id' => $plant->getId(),
                'name' => $plant->getName(),
                'quantity' => $quantity,
                'price' => $plant->getPrice(),
            ];
        }

        $orderDetails->setTotalPrice($totalPrice);
        $orderDetails->setPlantSummary($plantSummary);

        $entityManager->persist($order);
        $entityManager->persist($orderDetails);
        $entityManager->flush();

        return $this->json($order, Response::HTTP_CREATED, [], ['groups' => 'order:read']);
    }

    // Route pour que l'admin supprime une commande
    #[Route('/api/orders/{id}', name: 'api_orders_delete', methods: ['DELETE'])]
    public function destroy(Order $order, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($order);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
