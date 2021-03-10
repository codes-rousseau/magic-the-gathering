<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Color;
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

    /**
     * Récupère les cartes selon les données du filtre
     *
     * @param $filter
     * @return Card[] Returns an array of Type objects
     */
    public function findWithFilter($filter, $idSet): array
    {
        $query = $this->createQueryBuilder('c')
                    ->join(Color::class, 'color')
        ->andWhere('c.Set = :setId')
        ->setParameter('setId', $idSet);

        if($filter['name']) {
            $query
                ->andWhere($query->expr()->like('c.name', ':name'))
                ->setParameter('name', '%' . $filter['name'] . '%');
        }

        if($filter['type']) {
            $query
                ->andWhere('c.Type = :type')
                ->setParameter('type', $filter['type']);
        }

        if($filter['colors']) {
            foreach ($filter['colors'] as $key => $color) {
                $query
                    ->andWhere(':color' . $key . ' MEMBER OF c.color')
                    ->setParameter('color' . $key, $color);
            }
        }

        return $query
            ->getQuery()
            ->getResult()
            ;
    }
}
