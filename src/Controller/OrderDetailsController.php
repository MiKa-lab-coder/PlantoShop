<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\PlantRepository;
use App\Repository\OrderDetailsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderDetailsController extends AbstractController
{
    #[Route('/api/order-details', name: 'api_order_details_list', methods: ['GET'])]
    public function index(OrderDetailsRepository $orderDetailsRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $orderDetails = $orderDetailsRepository->findAll();
        return $this->json($orderDetails, Response::HTTP_OK, [], ['groups' => 'orderDetails:read']);
    }

    #[Route('/api/order-details/{id}', name: 'api_order_details_show', methods: ['GET'])]
    public function show(OrderDetailsRepository $orderDetailsRepository, OrderDetails $orderDetails): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $orderDetails);
        return $this->json($orderDetailsRepository->find($orderDetails), Response::HTTP_OK,
            [], ['groups' => 'orderDetails:read']);
    }

    #[Route('/api/order-details', name: 'api_order_details_create', methods: ['POST'])]
    public function create(
        Request                $request,
        SerializerInterface    $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface     $validator,
        PlantRepository        $plantRepository
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

    #[Route('/api/order-details/{id}', name: 'api_order_details_update', methods: ['PUT'])]
    public function update(
        Request                $request,
        OrderDetails           $orderDetails,
        SerializerInterface    $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface     $validator,
        PlantRepository        $plantRepository
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $orderDetails);

        $data = json_decode($request->getContent(), true);
        $plant = $plantRepository->find($data['plant_id']);
        if (!$plant) {
            return $this->json(['error' => 'Plant not found'], Response::HTTP_BAD_REQUEST);
        }

        $serializer->deserialize($request->getContent(), OrderDetails::class, 'json',
            ['object_to_populate' =>
                $orderDetails, 'groups' => 'orderDetails:write']);

        $errors = $validator->validate($orderDetails);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($orderDetails, Response::HTTP_OK, [], ['groups' => 'orderDetails:read']);

    }

    #[Route('/api/order-details/{id}', name: 'api_order_details_delete', methods: ['DELETE'])]
    public function destroy(OrderDetails $orderDetails, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('delete', $orderDetails);

        $entityManager->remove($orderDetails);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
