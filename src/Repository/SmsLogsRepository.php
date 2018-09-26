<?php

namespace App\Repository;

use App\Entity\SmsLogs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SmsLogs|null find($id, $lockMode = null, $lockVersion = null)
 * @method SmsLogs|null findOneBy(array $criteria, array $orderBy = null)
 * @method SmsLogs[]    findAll()
 * @method SmsLogs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmsLogsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SmsLogs::class);
    }

//    /**
//     * @return SmsLogs[] Returns an array of SmsLogs objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SmsLogs
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
