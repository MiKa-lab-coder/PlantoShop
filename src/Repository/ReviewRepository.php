<?php

namespace App\Repository;

use App\Document\Review;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

class ReviewRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    // Trouve une plante par son ID pour pouvoir l'associer à un avis.
    public function findByPlantId(int $plantId): array
    {
        return $this->findBy(['plantId' => $plantId], ['createdAt' => 'DESC']);
    }
}
