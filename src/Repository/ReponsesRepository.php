<?php

namespace App\Repository;
use App\Entity\Reponses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
// Import ReponsesRepository in ReponsesController

/**
 * @method Reponses|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reponses|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reponses[]    findAll()
 * @method Reponses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class ReponsesRepository extends ServiceEntityRepository
{
     // Update the constructor of ReponsesRepository
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reponses::class); // Update to Reponses::class
    }

    public function findOneById($query): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.idReponse LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }

    public function search($searchTerm)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.email LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%')
            ->getQuery()
            ->getResult();
    }

}