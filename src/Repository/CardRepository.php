<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    private QueryBuilder $qb;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function getTypesAvailable(int $setId): array
    {
        $this->initializeQueryBuilder();

        $this->filterBySet($setId);

        $this->qb
            ->select('c.type')
            ->groupBy('c.type');

        $results = $this->getResult();

        $types = [];

        foreach ($results as $result) {
            $types[] = $result['type'];
        }

        return $types;
    }

    public function filter(int $setId, ?string $name = null, ?string $color = null, ?string $type = null): array
    {
        $this->initializeQueryBuilder();

        $this->filterBySet($setId);

        if (!empty($name)) {
            $this->filterByName($name);
        }

        if (!empty($color)) {
            $this->filterByColor($color);
        }

        if (!empty($type)) {
            $this->filterByType($type);
        }

        return $this->getResult();
    }

    private function initializeQueryBuilder(): void
    {
        $this->qb = $this->createQueryBuilder('c');
    }

    private function getResult(): array
    {
        return $this->qb->getQuery()->getResult();
    }

    private function filterBySet(int $setId): void
    {
        $this->qb
            ->andWhere('c.set = :set')
            ->setParameter('set', $setId);
    }

    private function filterByName(string $name): void
    {
        $this->qb
            ->andWhere(
                $this->qb->expr()->like('c.name', ':name')
            )
            ->setParameter('name', '%' . $name . '%');
    }

    private function filterByType(string $type): void
    {
        $this->qb
            ->andWhere(
                $this->qb->expr()->like('c.type', ':type')
            )
            ->setParameter('type', '%' . $type . '%');
    }

    private function filterByColor(string $color): void
    {
        $this->qb
            ->andWhere(
                $this->qb->expr()->like('c.color', ':color')
            )
            ->setParameter('color', '%' . $color . '%');
    }
}
