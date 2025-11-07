<?php

namespace App\Repository;

use App\Entity\Plant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plant>
 */
class PlantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plant::class);
    }

    /**
     * Récupère toutes les plantes avec leurs relations (Category et Owner)
     * @return Plant[]
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('p') // 'p' est un alias pour Plant
            ->addSelect('c') // On dit à Doctrine de sélectionner aussi les données de la catégorie
            ->addSelect('o') // Et celles de l'owner (utilisateur)
            ->leftJoin('p.category', 'c') // On fait la jointure avec la catégorie
            ->leftJoin('p.owner', 'o') // On fait la jointure avec l'owner
            ->orderBy('p.name', 'ASC') // On trie par nom pour un affichage cohérent
            ->getQuery()
            ->getResult();
    }
}
