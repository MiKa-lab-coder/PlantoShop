<?php

namespace App\Controller;

use App\Entity\Plant;
use App\Repository\CategoryRepository;
use App\Repository\PlantRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/plants')]
class PlantController extends AbstractController
{
    #[Route('', name: 'api_plants_list', methods: ['GET'])]
    public function index(PlantRepository $plantRepository): JsonResponse
    {
        $plants = $plantRepository->findAllWithRelations();

        $context = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn ($object) => $object->getId(),
            'groups' => 'plant:read'
        ];

        return $this->json($plants, Response::HTTP_OK, [], $context);
    }

    #[Route('/{id}', name: 'api_plants_show', methods: ['GET'])]
    public function show(Plant $plant): JsonResponse
    {
        $context = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn ($object) => $object->getId(),
            'groups' => 'plant:read'
        ];
        return $this->json($plant, Response::HTTP_OK, [], $context);
    }

    #[Route('', name: 'api_plants_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CategoryRepository $categoryRepository,
        FileUploader $fileUploader // Injecter le service
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // On récupère les données du formulaire
        $data = $request->request->all();

        $category = $categoryRepository->find($data['category_id'] ?? 0);
        if (!$category) {
            return $this->json(['error' => 'Catégorie non valide.'], Response::HTTP_BAD_REQUEST);
        }

        // Création de la plante
        $plant = new Plant();
        $plant->setName($data['name']);
        $plant->setDescription($data['description']);
        $plant->setPrice($data['price']);
        $plant->setCategory($category);
        $plant->setOwner($this->getUser());

        // Gérer l'upload de fichier
        $imageFile = $request->files->get('imageFile');
        if ($imageFile) {
            $imageUrl = $fileUploader->upload($imageFile);
            $plant->setImageUrl($imageUrl);
        }

        $errors = $validator->validate($plant);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($plant);
        $entityManager->flush();

        return $this->json($plant, Response::HTTP_CREATED, [], ['groups' => 'plant:read']);
    }

    #[Route('/{id}', name: 'api_plants_update', methods: ['POST'])]
    public function update(
        Request $request,
        Plant $plant,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CategoryRepository $categoryRepository,
        FileUploader $fileUploader // Injecter le service
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $request->request->all();

        // Mise à jour des données de la plante
        $plant->setName($data['name'] ?? $plant->getName());
        $plant->setDescription($data['description'] ?? $plant->getDescription());
        $plant->setPrice($data['price'] ?? $plant->getPrice());

        if (isset($data['category_id'])) {
            $category = $categoryRepository->find($data['category_id']);
            if ($category) {
                $plant->setCategory($category);
            }
        }

        $imageFile = $request->files->get('imageFile');
        if ($imageFile) {
            $imageUrl = $fileUploader->upload($imageFile);
            $plant->setImageUrl($imageUrl);
        }

        $errors = $validator->validate($plant);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($plant, Response::HTTP_OK, [], ['groups' => 'plant:read']);
    }

    #[Route('/{id}', name: 'api_plants_delete', methods: ['DELETE'])]
    public function destroy(Plant $plant, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $entityManager->remove($plant);
        $entityManager->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
	
	#[Route('/search/{query}', name: 'api_plants_search', methods: ['GET'])]
	public function search(string $query, PlantRepository $plantRepository): JsonResponse
	{
    $plants = $plantRepository->createQueryBuilder('p')
        ->where('LOWER(p.name) LIKE :query')
        ->setParameter('query', '%' . mb_strtolower($query) . '%')
        ->getQuery()
        ->getResult();

    return $this->json($plants, Response::HTTP_OK, [], [
        'groups' => 'plant:read',
        AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn ($object) => $object->getId(),
    ]);
	}

	#[Route('/by-category/{id}', name: 'api_plants_by_category', methods: ['GET'])]
	public function byCategory(int $id, PlantRepository $plantRepository, CategoryRepository $categoryRepository): JsonResponse
	{
    $category = $categoryRepository->find($id);
    if (!$category) {
        return $this->json(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
    }

    $plants = $plantRepository->findBy(['category' => $category]);

    return $this->json($plants, Response::HTTP_OK, [], [
        'groups' => 'plant:read',
        AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn ($object) => $object->getId(),
    ]);
	}
}
