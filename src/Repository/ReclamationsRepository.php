<?php

namespace App\Repository;

use App\Entity\Reclamations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reclamations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reclamations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reclamations[]    findAll()
 * @method Reclamations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReclamationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamations::class);
    }

    // Ajouter une méthode pour trier les réclamations
    public function findAllSortedBy($sortBy, $sortOrder = 'ASC')
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.' . $sortBy, $sortOrder)
            ->getQuery()
            ->getResult();
    }

    // Ajouter une méthode pour rechercher des réclamations en fonction de critères multiples
    public function searchByCriteria($criteria)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        foreach ($criteria as $key => $value) {
            $queryBuilder
                ->andWhere('r.' . $key . ' = :' . $key)
                ->setParameter($key, $value);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    // Ajouter une méthode pour paginer les résultats
    public function findAllPaginated($page = 1, $limit = 10)
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
