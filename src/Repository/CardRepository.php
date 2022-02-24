<?php

namespace App\Repository;

use App\Classes\Search;
use App\Entity\Card;
use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    /**
     * @return Card[] Returns an array of Card objects
     */
    public function findByCollection(int $collection): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.collections', 'co')
            ->where('co.id = :collection')
            ->setParameter('collection', $collection)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCollectionAndForm(Search $search, int $collection)
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.collections', 'collections')
            ->where('collections.id = :collection')
            ->setParameter('collection', $collection);

        if (!empty($search->name)) {
            $qb->andWhere('c.name LIKE :name')
                ->setParameter('name', "%{$search->name}%");
        }

        if (!empty($search->color)) {
            $qb->leftJoin('c.colors', 'colors')
                ->andWhere('colors.id = :color')
                ->setParameter('color', $search->color->getId());
        }

        if (!empty($search->type)) {
            $qb->leftJoin(Type::class, 'type', Join::WITH, 'c.type = type.id')
                ->andWhere('type.id = :type')
                ->setParameter('type', $search->type->getId());
        }

        return $qb->getQuery()
            ->getResult();
    }
}
