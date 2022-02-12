<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /**
     * @return Card[]
     */
    public function findAllByCriteria(array $criteria): array
    {
        $criteria = $this->configureCriteriaResolver()->resolve($criteria);
        $expr = $this->_em->getExpressionBuilder();

        $qb = $this
            ->createQueryBuilder('card')
            ->addSelect('color')
            ->leftJoin('card.colors', 'color')
            ->where($expr->eq('card.set', ':setId'))
            ->setParameter('setId', $criteria['set_id'])
        ;

        if (!empty($criteria['card_name'])) {
            $qb
                ->andWhere($expr->like('card.name', ':searchName'))
                ->setParameter('searchName', '%'.$criteria['card_name'].'%')
            ;
        }

        if (!empty($criteria['card_type'])) {
            $qb
                ->andWhere($expr->like('card.type', ':searchType'))
                ->setParameter('searchType', '%'.$criteria['card_type'].'%')
            ;
        }

        if ($criteria['card_colors'] instanceof ArrayCollection) {
            $colors = $criteria['card_colors'];

            if (!$colors->isEmpty()) {
                $orWhere = [];

                foreach ($colors->toArray() as $index => $color) {
                    $variableName = sprintf(':colorAbbreviation%d', $index);
                    $orWhere[] = $expr->eq('color.abbreviation', $variableName);
                    $qb->setParameter($variableName, $color->getAbbreviation());
                }

                $qb->andWhere($expr->orX(...$orWhere));
            }
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    private function configureCriteriaResolver(): OptionsResolver
    {
        return (new OptionsResolver())
            ->setDefined([
                'set_id',
            ])
            ->setDefaults([
                'card_name' => null,
                'card_type' => null,
                'card_colors' => null,
            ])
            ->setRequired(['set_id'])
            ->setAllowedTypes('set_id', [UuidInterface::class])
            ->setAllowedTypes('card_name', ['string', 'null'])
            ->setAllowedTypes('card_type', ['string', 'null'])
            ->setAllowedTypes('card_colors', [ArrayCollection::class])
        ;
    }
}
