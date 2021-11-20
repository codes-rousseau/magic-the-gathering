<?php

namespace App\Repository;

use App\Entity\Carte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Carte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Carte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Carte[]    findAll()
 * @method Carte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carte::class);
    }

    public function findArticlesByName(string $texteRecherche, int $optionRecherche, int $idCollection)
    {
        $query = $this->createQueryBuilder('Carte')->andWhere('Carte.collection = :collection');

        // 0 : recherche par nom, 1: recherche par couleur, 2: recheche par type
        switch ($optionRecherche) {
            case 0:
                $query->andWhere('Carte.nom LIKE :recherche');
                break;
            case 1:
                $query->andWhere('Carte.couleur LIKE :recherche');
                break;
            case 2:
                $query->andWhere('Carte.type LIKE :recherche');
                break;
        }
        $query->setParameter('collection', $idCollection);
        $query->setParameter('recherche', '%' . $texteRecherche . '%');

        return $query->getQuery()->execute();
    }
}
