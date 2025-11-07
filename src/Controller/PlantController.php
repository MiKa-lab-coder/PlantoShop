<?php

namespace App\Controller;

use App\Entity\Plant;
use App\Entity\Category;
use App\Repository\PlantRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlantController extends AbstractController
{
    #[Route('/api/plants', name: 'api_plants_list', methods: ['GET'])]
    public function index(PlantRepository $plantRepository): JsonResponse
    {

        $plants = $plantRepository->findAllWithRelations();
        return $this->json($plants, Response::HTTP_OK, [], ['groups' => 'plant:read']);
    }

    #[Route('/api/plants/search/{query}', name: 'api_plants_search', methods: ['GET'])]
    public function search(string $query, PlantRepository $plantRepository): JsonResponse
    {

        $plants = $plantRepository->createQueryBuilder('p')
            ->where('p.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        return $this->json($plants, Response::HTTP_OK, [], ['groups' => 'plant:read']);
    }

    #[Route('/api/plants/by-category/{id}', name: 'api_plants_by_category', methods: ['GET'])]
    public function byCategory(Category $category): JsonResponse
    {
        // Doctrine gère automatiquement la récupération des plantes associées à la catégorie.
        $plants = $category->getPlants();
        return $this->json($plants, Response::HTTP_OK, [], ['groups' => 'plant:read']);
    }

    #[Route('/api/plants/{id}', name: 'api_plants_show', methods: ['GET'])]
    public function show(Plant $plant): JsonResponse
    {
        return $this->json($plant, Response::HTTP_OK, [], ['groups' => 'plant:read']);
    }

    #[Route('/api/plants', name: 'api_plants_create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CategoryRepository $categoryRepository
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);
        $plant = $serializer->deserialize($request->getContent(), Plant::class, 'json', ['groups' => 'plant:write']);

        if (empty($data['category_id'])) {
            return $this->json(['error' => 'category_id is required'], Response::HTTP_BAD_REQUEST);
        }

        $category = $categoryRepository->find($data['category_id']);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], Response::HTTP_BAD_REQUEST);
        }
        $plant->setCategory($category);

        // Sécurité : Le propriétaire est toujours l'utilisateur connecté
        $plant->setOwner($this->getUser());

        $errors = $validator->validate($plant);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($plant);
        $entityManager->flush();

        return $this->json($plant, Response::HTTP_CREATED, [], ['groups' => 'plant:read']);
    }

    #[Route('/api/plants/{id}', name: 'api_plants_update', methods: ['PUT'])]
    public function update(
        Request $request,
        Plant $plant,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CategoryRepository $categoryRepository
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);
        $serializer->deserialize($request->getContent(), Plant::class, 'json', ['object_to_populate' => $plant, 'groups' => 'plant:write']);

        // Gérer la mise à jour de la catégorie si elle est fournie
        if (isset($data['category_id'])) {
            $category = $categoryRepository->find($data['category_id']);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], Response::HTTP_BAD_REQUEST);
            }
            $plant->setCategory($category);
        }

        $errors = $validator->validate($plant);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($plant, Response::HTTP_OK, [], ['groups' => 'plant:read']);
    }

    #[Route('/api/plants/{id}', name: 'api_plants_delete', methods: ['DELETE'])]
    public function destroy(Plant $plant, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($plant);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
