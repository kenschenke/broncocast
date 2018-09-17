<?php

namespace App\Repository;

use App\Entity\Carriers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Carriers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Carriers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Carriers[]    findAll()
 * @method Carriers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarriersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Carriers::class);
    }

//    /**
//     * @return Carriers[] Returns an array of Carriers objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Carriers
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
