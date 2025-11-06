<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Plant;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\PlantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/cart')]
class CartController extends AbstractController
{
    private function getOrCreateCart(EntityManagerInterface $entityManager): Cart
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder au panier.');
        }

        $cart = $user->getCart();
        if (!$cart) {
            $cart = new Cart();
            $cart->setOwner($user);
            $entityManager->persist($cart);
            // No flush needed here, it will be flushed with the item operations
        }
        return $cart;
    }

    #[Route('', name: 'api_cart_show', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager): JsonResponse
    {
        $cart = $this->getOrCreateCart($entityManager);
        return $this->json($cart, Response::HTTP_OK, [], ['groups' => 'cart:read']);
    }

    #[Route('/items', name: 'api_cart_add_item', methods: ['POST'])]
    public function addItem(Request $request, PlantRepository $plantRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $plantId = $data['plantId'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        $plant = $plantRepository->find($plantId);
        if (!$plant) {
            return $this->json(['error' => 'Plante non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $cart = $this->getOrCreateCart($entityManager);

        // Vérifier si l'article existe déjà
        $existingItem = null;
        foreach ($cart->getItems() as $item) {
            if ($item->getPlant() === $plant) {
                $existingItem = $item;
                break;
            }
        }

        // Mettre à jour la quantité si l'article existe déjà
        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
        } else {
            $newItem = new CartItem();
            $newItem->setPlant($plant);
            $newItem->setQuantity($quantity);
            $cart->addItem($newItem);
        }

        $entityManager->flush();
        return $this->json($cart, Response::HTTP_OK, [], ['groups' => 'cart:read']);
    }

    #[Route('/items/{id}', name: 'api_cart_update_item', methods: ['PUT'])]
    public function updateItem(CartItem $item, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier si l'utilisateur est propriétaire du panier
        if ($item->getCart()->getOwner() !== $this->getUser()) {
            return $this->json(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'] ?? null;

        if ($quantity === null || !is_int($quantity) || $quantity < 1) {
            return $this->json(['error' => 'Quantité invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Mettre à jour la quantité
        $item->setQuantity($quantity);
        $entityManager->flush();

        return $this->json($item->getCart(), Response::HTTP_OK, [], ['groups' => 'cart:read']);
    }

    #[Route('/items/{id}', name: 'api_cart_remove_item', methods: ['DELETE'])]
    public function removeItem(CartItem $item, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier si l'utilisateur est propriétaire du panier
        if ($item->getCart()->getOwner() !== $this->getUser()) {
            return $this->json(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }

        // Supprimer l'article du panier
        $cart = $item->getCart();
        $cart->removeItem($item);
        $entityManager->flush();

        return $this->json($cart, Response::HTTP_OK, [], ['groups' => 'cart:read']);
    }

    #[Route('/merge', name: 'api_cart_merge', methods: ['POST'])]
    public function merge(Request $request, PlantRepository $plantRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $localCartItems = json_decode($request->getContent(), true);
        if (!is_array($localCartItems)) {
            return $this->json(['error' => 'Données du panier local invalides'], Response::HTTP_BAD_REQUEST);
        }

        $cart = $this->getOrCreateCart($entityManager);

        foreach ($localCartItems as $localItem) {
            $plant = $plantRepository->find($localItem['plantId'] ?? null);
            if (!$plant) continue; // Skip if plant not found

            $quantity = $localItem['quantity'] ?? 1;

            $existingItem = null;
            foreach ($cart->getItems() as $item) {
                if ($item->getPlant() === $plant) {
                    $existingItem = $item;
                    break;
                }
            }

            if ($existingItem) {
                $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
            } else {
                $newItem = new CartItem();
                $newItem->setPlant($plant);
                $newItem->setQuantity($quantity);
                $cart->addItem($newItem);
            }
        }

        $entityManager->flush();
        return $this->json($cart, Response::HTTP_OK, [], ['groups' => 'cart:read']);
    }
}
