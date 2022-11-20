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

    /**
     * Find cards by Card object
     * @param Card $card
     * @return float|int|mixed|string
     */
    public function findByForm(Card $card) {

         $q = $this->createQueryBuilder('c')
            ->andWhere('c.collection = :collection_id')
            ->setParameter('collection_id', $card->getCollection()->getId())
            ->orderBy('c.name', 'ASC')
            ;


         if(!is_null($card->getName())) {
             $q->andWhere('c.name LIKE :name')
                ->setParameter('name', '%' . $card->getName() . '%')
             ;
         }


         if(!is_null($card->getType())) {
            $q->andWhere('c.type = :type_id')
                ->setParameter('type_id', $card->getType()->getId());
         }


         if(count($card->getColor()) > 0) {
             $colors = [];
             foreach($card->getColor() as $color) {
                 $colors[] = $color->getId();
             }

             $q->leftJoin('c.color', 'co')
                 ->andWhere('co.id IN ( :colors )')
                 ->setParameter('colors', $colors);
         }

         $query = $q->getQuery();

         return $query->getResult();
    }
}
