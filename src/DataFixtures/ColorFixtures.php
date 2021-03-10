<?php

namespace App\DataFixtures;

use App\Entity\Color;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ColorFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $colors = [
            'W' => 'White',
            'U' => 'Blue',
            'B' => 'Black',
            'R' => 'Red',
            'G' => 'Green'
        ];

        foreach ($colors as $code => $name) {
            $color = new Color();
            $color->setCode($code);
            $color->setName($name);
            $manager->persist($color);
        }

        $manager->flush();
    }
}
