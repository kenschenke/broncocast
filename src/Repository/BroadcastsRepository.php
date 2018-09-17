<?php

namespace App\Repository;

use App\Entity\Broadcasts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Broadcasts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Broadcasts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Broadcasts[]    findAll()
 * @method Broadcasts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BroadcastsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Broadcasts::class);
    }

//    /**
//     * @return Broadcasts[] Returns an array of Broadcasts objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Broadcasts
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
