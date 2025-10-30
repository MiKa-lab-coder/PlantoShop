<?php

namespace App\Controller;

use App\Entity\Plant;
use App\Repository\PlantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlantController extends AbstractController
{
    #[Route('/api/plants', name: 'api_plants_list', methods: ['GET'])]
    public function index(
        PlantRepository $plantRepository
    ): JsonResponse
    {
        $plants = $plantRepository->findAll();
        
        return $this->json($plants, JsonResponse::HTTP_OK, [], ['groups' => 'plant:read']);
    }

    #[Route('/api/plants/{id}', name: 'api_plants_show', methods: ['GET'])]
    public function show(
        int $id,
        PlantRepository $plantRepository
    ): JsonResponse
    {
        $plant = $plantRepository->find($id);

        if (!$plant) {
            throw $this->createNotFoundException('Plant not found');
        }

        return $this->json($plant, JsonResponse::HTTP_OK, [], ['groups' => 'plant:read']);
    }
    
    #[Route('/api/plants', name: 'api_plants_create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse
    {
        // Désérialise le contenu JSON en un tableau associatif pour extraire les IDs de relations
        $data = json_decode($request->getContent(), true);

        // Désérialise le reste des données en un objet Plant
        $plant = $serializer->deserialize($request->getContent(), Plant::class, 'json', ['groups' => 'plant:write']);
        
        // Gérer la relation Category
        if (isset($data['category_id'])) {
            $category = $categoryRepository->find($data['category_id']);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], JsonResponse::HTTP_BAD_REQUEST);
            }
            $plant->setCategory($category);
        } else {
            return $this->json(['error' => 'category_id is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Gérer la relation Owner (User)
        if (isset($data['owner_id'])) {
            $owner = $userRepository->find($data['owner_id']);
            if (!$owner) {
                return $this->json(['error' => 'Owner (User) not found'], JsonResponse::HTTP_BAD_REQUEST);
            }
            $plant->setOwner($owner);
        } else {
            return $this->json(['error' => 'owner_id is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($plant);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $entityManager->persist($plant);
        $entityManager->flush();
        
        return $this->json($plant, JsonResponse::HTTP_CREATED, [], ['groups' => 'plant:read']);
    }
    
    #[Route('/api/plants/{id}', name: 'api_plants_update', methods: ['PUT'])]
    public function update(
        Request $request,
        int $id,
        PlantRepository $plantRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CategoryRepository $categoryRepository,
        UserRepository $userRepository
    ): JsonResponse
    {
        $plant = $plantRepository->find($id);

        if (!$plant) {
            throw $this->createNotFoundException('Plant not found');
        }

        // Désérialise le contenu JSON en un tableau associatif pour extraire les IDs de relations
        $data = json_decode($request->getContent(), true);

        // Désérialise le reste des données sur l'objet Plant existant
        $serializer->deserialize($request->getContent(), Plant::class, 'json',
            ['object_to_populate' => $plant, 'groups' => 'plant:write']);
        
        // Gérer la relation Category
        if (isset($data['category_id'])) {
            $category = $categoryRepository->find($data['category_id']);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], JsonResponse::HTTP_BAD_REQUEST); // Code 400
            }
            $plant->setCategory($category);
        }

        // Gérer la relation Owner (User)
        if (isset($data['owner_id'])) {
            $owner = $userRepository->find($data['owner_id']);
            if (!$owner) {
                return $this->json(['error' => 'Owner (User) not found'], JsonResponse::HTTP_BAD_REQUEST);
            }
            $plant->setOwner($owner);
        }

        $errors = $validator->validate($plant);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->flush(); // Enregistre les modifications

        return $this->json($plant, JsonResponse::HTTP_OK, [], ['groups' => 'plant:read']); // Code 200 OK
    }
    
    #[Route('/api/plants/{id}', name: 'api_plants_delete', methods: ['DELETE'])]
    public function destroy(
        int $id,
        PlantRepository $plantRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $plant = $plantRepository->find($id);

        if (!$plant) {
            throw $this->createNotFoundException('Plant not found');
        }

        $entityManager->remove($plant);
        $entityManager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT); // Code 204
    }
}