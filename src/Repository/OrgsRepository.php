<?php

namespace App\Repository;

use App\Entity\Orgs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Orgs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orgs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orgs[]    findAll()
 * @method Orgs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrgsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Orgs::class);
    }

//    /**
//     * @return Orgs[] Returns an array of Orgs objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Orgs
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
