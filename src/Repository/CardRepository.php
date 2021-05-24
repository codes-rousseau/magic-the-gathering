<?php

namespace App\Repository;

use App\Classe\Search;
use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
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

    public function findWithSearch(Search $search)
    {
        $query = $this
            ->createQueryBuilder('card')
            ->select('card', 'type', 'color')
            ->join('card.type_line', 'type')
            ->leftJoin('card.color', 'color');

        if (!empty($search->name)) {
            $query = $query
                ->andWhere('card.name LIKE :name')
                ->setParameter('name', "%{$search->name}%");
        }

        if (!empty($search->type)) {
            $query = $query
                ->andWhere('type.id IN (:type_line)')
                ->setParameter('type_line', $search->type);
        }

        if (!empty($search->color)) {
            $query = $query
                ->andWhere('color.id IN (:name)')
                ->setParameter('name', $search->color);
        }

        return $query->getQuery()->getResult();
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
