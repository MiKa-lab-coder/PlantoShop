<?php

namespace App\Controller;

use App\Document\Review;
use App\Entity\Order;
use App\Repository\PlantRepository;
use App\Repository\ReviewRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class ReviewController extends AbstractController
{
    #[Route('/plants/{plantId}/reviews', name: 'api_reviews_list_for_plant', methods: ['GET'])]
    public function listForPlant(int $plantId, ReviewRepository $reviewRepository): JsonResponse
    {
        $reviews = $reviewRepository->findByPlantId($plantId);
        return $this->json($reviews, Response::HTTP_OK, [], ['groups' => 'review:read']);
    }

    #[Route('/reviews', name: 'api_reviews_create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        DocumentManager $documentManager,
        PlantRepository $plantRepository,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        /** @var Review $review */
        $review = $serializer->deserialize($request->getContent(), Review::class, 'json',
            ['groups' => 'review:write']);

        $plant = $plantRepository->find($review->getPlantId());
        if (!$plant) {
            return $this->json(['error' => 'Plant not found.'], Response::HTTP_NOT_FOUND);
        }

        // Verification de la commande
        $hasOrderedPlant = false;
        /** @var Order $order */
        foreach ($user->getOrders() as $order) {
            if ($order->getCart() && $order->getCart()->getPlants()->contains($plant)) {
                $hasOrderedPlant = true;
                break;
            }
        }

        if (!$hasOrderedPlant) {
            return $this->json(['error' => 'You can only review plants you have purchased.'],
                Response::HTTP_FORBIDDEN);
        }

        $review->setUserId($user->getId());
        $review->setUsername($user->getFirstName());
        $review->setPlantId($plant->getId()); // Assure que l'ID est correct

        $errors = $validator->validate($review);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $documentManager->persist($review);
        $documentManager->flush();

        return $this->json($review, Response::HTTP_CREATED, [], ['groups' => 'review:read']);
    }
}
