<?php

namespace App\Service;

use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;

class SetService
{
    private const SET_ICONS_URL = '/public/sets/';

    private EntityManagerInterface $em;
    private string $projectDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $projectDir
    )
    {
        $this->em = $entityManager;
        $this->projectDir = $projectDir;
    }

    /**
     * Crée un Set en base de données s'il n'existe pas déjà.
     * @return Set|null si le Set n'a pas pu être créé.
     */
    public function createSet(Set $set): Set|null
    {
        $setRepository = $this->em->getRepository(Set::class);
        if($setRepository->findOneBy(['code'=>$set->getCode()]))
        {
            return null;
        }

        $iconFile = file_get_contents($set->getIconSvgURI());
        $iconPath = $this->projectDir . self::SET_ICONS_URL . $set->getIcon();
        file_put_contents($iconPath, $iconFile);

        $this->em->persist($set);
        $this->em->flush();
        return $set;
    }

}
