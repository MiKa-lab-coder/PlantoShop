<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'api_users_list', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        // Le helper $this->json() s'occupe de sérialiser
        return $this->json($users, JsonResponse::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/api/users/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id); // Correction: Utilisation de find()

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->json($user, JsonResponse::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/api/users', name: 'api_users_create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        // Désérialise le contenu JSON en objet User
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', ['groups' => 'user:write']);

        // Validation du nouvel utilisateur
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user); // Persiste le nouvel utilisateur
        $entityManager->flush(); // Enregistre dans la base de données

        return $this->json($user, JsonResponse::HTTP_CREATED, [], ['groups' => 'user:read']);
    }

    #[Route('/api/users/{id}', name: 'api_users_update', methods: ['PUT'])]
    public function update(
        Request $request,
        int $id,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $userRepository->find($id); // Récupère l'utilisateur existant

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Désérialise le contenu JSON sur l'objet User existant
        $serializer->deserialize($request->getContent(), User::class, 'json',
            ['object_to_populate' => $user, 'groups' => 'user:write']);

        // Validation des modifications
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->flush(); // Enregistre les modifications

        return $this->json($user, JsonResponse::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/api/users/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    public function destroy(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id); // Récupère l'utilisateur

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $entityManager->remove($user); // Supprime l'utilisateur
        $entityManager->flush(); // Enregistre la suppression

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT); // Retourne un 204 No Content
    }
}
