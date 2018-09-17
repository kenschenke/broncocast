<?php

namespace App\Repository;

use App\Entity\OrgMembers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrgMembers|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrgMembers|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrgMembers[]    findAll()
 * @method OrgMembers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrgMembersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrgMembers::class);
    }

//    /**
//     * @return OrgMembers[] Returns an array of OrgMembers objects
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
    public function findOneBySomeField($value): ?OrgMembers
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
