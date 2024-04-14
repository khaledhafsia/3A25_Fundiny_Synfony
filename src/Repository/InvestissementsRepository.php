<?php

namespace App\Repository;

use App\Entity\Investissements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Investissements>
 *
 * @method Investissements|null find($id, $lockMode = null, $lockVersion = null)
 * @method Investissements|null findOneBy(array $criteria, array $orderBy = null)
 * @method Investissements[]    findAll()
 * @method Investissements[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvestissementsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Investissements::class);
    }
    
    public function findByDescription($searchTerm)
{
    return $this->createQueryBuilder('i')
        ->andWhere('i.description LIKE :searchTerm')
        ->setParameter('searchTerm', '%'.$searchTerm.'%')
        ->getQuery()
        ->getResult();
}

//    /**
//     * @return Investissements[] Returns an array of Investissements objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Investissements
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
