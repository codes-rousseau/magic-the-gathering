<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\CardSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /**
     * Récupére les différents types de carte dans la collection spécifiée
     * @param CardSet $set
     * @return mixed[]
     */
    public function getTypesForSet(CardSet $set) {
        $qb = $this->createQueryBuilder('c')
            ->join("c.type", "t")
            ->select("t.id, t.name")
            ->distinct()
            ->andWhere("c.set = :set")
            ->setParameter('set', $set);
        return $qb->getQuery()->getScalarResult();
    }

    /**
     *
     * @param $criteria
     * @return Collection[Card]
     */
    public function getAllCards($criteria) {
        $qb = $this->createQueryBuilder('c');
        if($criteria['type']??false) {
            $qb->join("c.type", "t");
            $qb->andWhere('t.id = :type')
                ->setParameter('type', $criteria['type']);
        }
        if($criteria['name']??false) {
            $qb->andWhere('c.name like :name')
                ->setParameter('name', '%'.$criteria['name'].'%');
        }
        if($criteria['color']??false) {
            $qb->join('c.colors', 'cl')
                ->andWhere('cl.id = :id')
                ->setParameter('id', $criteria['color']);
        }
        if($criteria['set']??false) {
            $qb->andWhere("c.set = :set")
                ->setParameter('set', $criteria['set']);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Card $entity, bool $flush = true): void
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
    public function remove(Card $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Card[] Returns an array of Card objects
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
    public function findOneBySomeField($value): ?Card
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
