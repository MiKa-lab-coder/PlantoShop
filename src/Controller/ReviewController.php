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
    // Récuperation des avis d'une plante
    #[Route('/plants/{plantId}/reviews', name: 'api_reviews_list_for_plant', methods: ['GET'])]
    public function listForPlant(int $plantId, ReviewRepository $reviewRepository): JsonResponse
    {
        $reviews = $reviewRepository->findBy(['plantId' => $plantId], ['createdAt' => 'DESC']);
        return $this->json($reviews, Response::HTTP_OK, [], ['groups' => 'review:read']);
    }

    // Récuperation des avis de l'utilisateur
    #[Route('/user/reviews', name: 'api_user_reviews_list', methods: ['GET'])]
    public function listUserReviews(ReviewRepository $reviewRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        
        $reviews = $reviewRepository->findBy(['userId' => $user->getId()], ['createdAt' => 'DESC']);
        return $this->json($reviews, Response::HTTP_OK, [], ['groups' => 'review:read']);
    }

    // Création d'un avis pour une plante
    #[Route('/plants/{plantId}/reviews', name: 'api_reviews_create_for_plant', methods: ['POST'])]
    public function create(
        $plantId,
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
            if ($order->getPlants()->contains($plant)) {
                $hasOrderedPlant = true;
                break;
            }
        }

        if (!$hasOrderedPlant) {
            return $this->json(['error' => 'Vous ne pouvez laisser un avis que pour les plantes que vous avez achetées.'],
                Response::HTTP_FORBIDDEN);
        }
        
        // Vérification si un avis existe déjà pour cette plante par cet utilisateur
        $existingReview = $documentManager->getRepository(Review::class)->findOneBy([
            'userId' => $user->getId(),
            'plantId' => $plantId
        ]);

        if ($existingReview) {
            return $this->json(['error' => 'Vous avez déjà laissé un avis pour cette plante.'],
                Response::HTTP_CONFLICT);
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

    // Modifier un avis
    #[Route('/reviews/{id}', name: 'api_reviews_update', methods: ['PUT'])]
    public function update(
        $id,
        Request $request,
        SerializerInterface $serializer,
        DocumentManager $documentManager,
        ReviewRepository $reviewRepository,
        ValidatorInterface $validator
        ): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        /** @var Review $review */
        $review = $reviewRepository->find($id);
        if (!$review) {
            return $this->json(['error' => 'Avis non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        if ($review->getUserId() !== $user->getId()) {
            return $this->json(['error' => 'Vous ne pouvez pas modifier cet avis']);
        }

        $serializer->deserialize($request->getContent(), Review::class, 'json',
            ['object_to_populate' => $review, 'groups' => 'review:write']);

        $errors = $validator->validate($review);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $documentManager->flush();

        return $this->json($review, Response::HTTP_OK, [], ['groups' => 'review:read']);
    }

    // Supprimer un avis
    #[Route('/reviews/{id}', name: 'api_reviews_delete', methods: ['DELETE'])]
    public function destroy($id, DocumentManager $documentManager, ReviewRepository $reviewRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var Review $review */
        $review = $reviewRepository->find($id);
        if (!$review) {
            return $this->json(['error' => 'Avis non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $documentManager->remove($review);
        $documentManager->flush();

        return $this->json(['message' => 'Avis supprimé avec succès.'], Response::HTTP_OK);
    }
}
