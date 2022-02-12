<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Set;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class SetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Set::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findWithAllJoin(UuidInterface $id): ?Set
    {
        return $this
            ->getBuilderOneSetWithAllJoins($id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getBuilderOneSetWithAllJoins(UuidInterface $id): QueryBuilder
    {
        $expr = $this->_em->getExpressionBuilder();

        return $this
            ->createQueryBuilder('s')
            ->addSelect('card', 'color')
            ->leftJoin('s.cards', 'card')
            ->leftJoin('card.colors', 'color')
            ->where($expr->eq('s.id', ':id'))
            ->setParameter('id', $id)
        ;
    }
}
