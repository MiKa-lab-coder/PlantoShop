<?php

namespace App\Controller;

use App\Entity\Plant;
use App\Entity\User;
use App\Entity\Cart;
use App\Entity\Order;
use App\Repository\PlantRepository;
use App\Repository\UserRepository;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends AbstractController
{
    #[Route('/api/orders', name: 'api_orders_list', methods: ['GET'])]
    public function index
    (
        OrderRepository $orderRepository
    ): JsonResponse
    {
       $this->denyAccessUnlessGranted('ROLE_ADMIN');
       $order = $orderRepository->findAll();
       return $this->json($order, Response::HTTP_OK, [], ['groups' => 'order:read']);
    }

    #[Route('/api/orders/{id}', name: 'api_orders_show', methods: ['GET'])]
    public function show(Order $order): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $order);
        return $this->json($order, Response::HTTP_OK, [], ['groups' => 'order:read']);
    }

    #[Route('/api/orders', name: 'api_orders_create', methods: ['POST'])]
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

        $order = $this->getUser()->getOrder();
        if (!$order) {
            $order = new Order();
            $order->setClient($this->getUser());
            $entityManager->persist($order);
        }

        $order->addPlant($plant);

        $errors = $validator->validate($order);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
                }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($order, Response::HTTP_CREATED, [], ['groups' => 'order:read']);
    }

    #[Route('/api/orders/{id}', name: 'api_orders_update', methods: ['PUT'])]
    public function update(
        Request $request,
        Order $order,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        PlantRepository $plantRepository
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $order);

        $data = json_decode($request->getContent(), true);
        $plant = $plantRepository->find($data['plant_id']);
        if (!$plant) {
            return $this->json(['error' => 'Plant not found'], Response::HTTP_BAD_REQUEST);
            }

        $serializer->deserialize($request->getContent(), Order::class, 'json',
            ['object_to_populate' => $order, 'groups' => 'order:write']);


        $errors = $validator->validate($order);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
                }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($order, Response::HTTP_OK, [], ['groups' => 'order:read']);
    }

    #[Route('/api/orders/{id}', name: 'api_orders_delete', methods: ['DELETE'])]
    public function destroy(Order $order, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('delete', $order);

        $entityManager->remove($order);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}