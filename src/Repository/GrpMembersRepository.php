<?php

namespace App\Repository;

use App\Entity\GrpMembers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GrpMembers|null find($id, $lockMode = null, $lockVersion = null)
 * @method GrpMembers|null findOneBy(array $criteria, array $orderBy = null)
 * @method GrpMembers[]    findAll()
 * @method GrpMembers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrpMembersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GrpMembers::class);
    }

//    /**
//     * @return GrpMembers[] Returns an array of GrpMembers objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GrpMembers
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
