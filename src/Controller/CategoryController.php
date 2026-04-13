<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends AbstractController
{
    #[Route('/api/categories', name: 'api_categories_list', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        return $this->json($categories, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('/api/categories/search/{query}', name: 'api_categories_search', methods: ['GET'])]
    public function search(string $query, CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->createQueryBuilder('c')
            ->where('LOWER(c.name) LIKE :query')
            ->setParameter('query', '%' . mb_strtolower($query) . '%')
            ->getQuery()
            ->getResult();

        return $this->json($categories, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('/api/categories/{id}', name: 'api_categories_show', methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('/api/categories', name: 'api_categories_create', methods: ['POST'])]
    public function create(
        Request                $request,
        SerializerInterface    $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface     $validator
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $category = $serializer->deserialize($request->getContent(), Category::class, 'json',
            ['groups' => 'category:write']);

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json($category, Response::HTTP_CREATED, [], ['groups' => 'category:read']);
    }

    #[Route('/api/categories/{id}', name: 'api_categories_update', methods: ['PUT'])]
    public function update(
        Request                $request,
        Category               $category,
        SerializerInterface    $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface     $validator
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $serializer->deserialize($request->getContent(), Category::class, 'json',
            ['object_to_populate' => $category, 'groups' => 'category:write']);

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $entityManager->flush();

        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('/api/categories/{id}', name: 'api_categories_delete', methods: ['DELETE'])]
    public function destroy(Category $category, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($category);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
