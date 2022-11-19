<?php

namespace App\Repository;

use App\Entity\Collections;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collections>
 *
 * @method Collections|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collections|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collections[]    findAll()
 * @method Collections[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollectionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collections::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Collections $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Collections $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Collections[] Returns an array of Collections objects
    //  */
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
    public function findOneBySomeField($value): ?Collections
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
