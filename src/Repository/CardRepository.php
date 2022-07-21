<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    //  * @return Card[]
    //  */
    public function applyFilter(array $criteria)
    {
        $expr = $this->_em->getExpressionBuilder();

        $query = $this->createQueryBuilder('card')
            ->addSelect('color')
            ->leftJoin('card.colors', 'color')
            ->where($expr->eq('card.set', ':setId'))
            ->setParameter('setId', $criteria['set']);

            if (array_key_exists('name', $criteria)) {
                $query
                    ->andWhere($expr->like('card.name', ':name'))
                    ->setParameter('name', '%'.$criteria['name'].'%');
            }
            if (array_key_exists('type', $criteria)) {
                $query
                    ->andWhere($expr->like('card.type', ':type'))
                    ->setParameter('type', '%'.$criteria['type'].'%');
            }
            if (array_key_exists('colors', $criteria) && count($criteria['colors']) > 0) {    
                $orWhere = [];
                foreach ($criteria['colors']->toArray() as $i => $color) {
                    $orWhere[] = $expr->eq('color.abbr', ':abbr' . $i);
                    $query->setParameter(':abbr' . $i, $color->getAbbr());
                }
                $query->andWhere($expr->orX(...$orWhere));
            }
    
            return $query->getQuery()->getResult();
    }

}
