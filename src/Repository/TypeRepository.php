<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Type|null find($id, $lockMode = null, $lockVersion = null)
 * @method Type|null findOneBy(array $criteria, array $orderBy = null)
 * @method Type[]    findAll()
 * @method Type[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    /**
     * @param $idSet
     * @return Type[] Returns an array of Type objects
     */
    public function findAllInSet($idSet): array
    {
        return $this->createQueryBuilder('t')
            ->join(Card::class, 'c')
            ->andWhere('c.Type = t.id')
            ->andWhere('c.Set = :idSet')
            ->setParameter('idSet', $idSet)
            ->groupBy('t.name')
            ->orderBy('t.name')
            ->getQuery()
            ->getResult()
        ;
    }

}
