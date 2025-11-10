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
        $reviews = $reviewRepository->findBy(['plantId' => $plantId], ['createdAt' => 'DESC']);
        return $this->json($reviews, Response::HTTP_OK, [], ['groups' => 'review:read']);
    }

    #[Route('/plants/{plantId}/reviews', name: 'api_reviews_create_for_plant', methods: ['POST'])]
    public function create(
        int $plantId,
        Request $request,
        SerializerInterface $serializer,
        DocumentManager $documentManager,
        PlantRepository $plantRepository,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $plant = $plantRepository->find($plantId);
        if (!$plant) {
            return $this->json(['error' => 'Plant not found.'], Response::HTTP_NOT_FOUND);
        }

        // Vérification si l'utilisateur a bien commandé cette plante
        $hasOrderedPlant = false;
        /** @var Order $order */
        foreach ($user->getOrders() as $order) {
            // Nouvelle logique de vérification
            if ($order->getPlants()->contains($plant)) {
                $hasOrderedPlant = true;
                break;
            }
        }

        if (!$hasOrderedPlant) {
            return $this->json(['error' => 'Vous ne pouvez laisser un avis que pour les plantes que vous avez achetées.'],
                Response::HTTP_FORBIDDEN);
        }

        /** @var Review $review */
        $review = $serializer->deserialize($request->getContent(), Review::class, 'json',
            ['groups' => 'review:write']);
        
        $review->setUserId($user->getId());
        $review->setUsername($user->getFirstName());
        $review->setPlantId($plant->getId());

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
