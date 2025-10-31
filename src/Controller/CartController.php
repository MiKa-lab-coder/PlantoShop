<?php

namespace App\Controller;

use App\Entity\Plant;
use App\Entity\User;
use App\Entity\Cart;
use App\Repository\PlantRepository;
use App\Repository\UserRepository;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CartController extends AbstractController
{
    #[Route('/api/carts', name: 'api_carts_list', methods: ['GET'])]
    public function index(CartRepository $cartRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $carts = $cartRepository->findAll();
        return $this->json($carts, Response::HTTP_OK, [], ['groups' => 'cart:read']);
    }

    #[Route('/api/carts/{id}', name: 'api_carts_show', methods: ['GET'])]
    public function show(Cart $cart): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $cart);
        return $this->json($cart, Response::HTTP_OK, [], ['groups' => 'cart:read']);
    }

    #[Route('/api/carts', name: 'api_carts_create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        PlantRepository $plantRepository
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $plant = $plantRepository->find($data['plant_id']);
        if (!$plant) {
            return $this->json(['error' => 'Plant not found'], Response::HTTP_BAD_REQUEST);
        }

        $cart = $this->getUser()->getCart();
        if (!$cart) {
            $cart = new Cart();
            $cart->setOwner($this->getUser());
            $entityManager->persist($cart);
        }

        $cart->addPlant($plant);

        $errors = $validator->validate($cart);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($cart, Response::HTTP_CREATED, [], ['groups' => 'cart:read']);
    }

    #[Route('/api/carts/{id}', name: 'api_carts_update', methods: ['PUT'])]
    public function update(
        Request $request,
        Cart $cart,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        PlantRepository $plantRepository
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $cart);

        $data = json_decode($request->getContent(), true);
        $plant = $plantRepository->find($data['plant_id']);
        if (!$plant) {
            return $this->json(['error' => 'Plant not found'], Response::HTTP_BAD_REQUEST);
        }

        $serializer->deserialize($request->getContent(), Cart::class, 'json',['object_to_populate' => $cart,
            'groups' => 'cart:write']);

        $errors = $validator->validate($cart);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($cart, Response::HTTP_OK, [], ['groups' => 'cart:read']);
    }
    
    #[Route('/api/carts/{id}', name: 'api_carts_delete', methods: ['DELETE'])]
    public function destroy(Cart $cart, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('delete', $cart);

        $entityManager->remove($cart);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
