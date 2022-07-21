<?php

use App\Service\ScryfallApiService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ScryfallApiServiceTest extends KernelTestCase
{
    public function testGetSetsByName()
    {
        self::bootKernel();

        $container = self::$container;

        $apiService = $container->get(ScryfallApiService::class);
        $sets = $apiService->getSetsByName('th');
        $this->assertIsArray($sets);
        $this->assertGreaterThan(0, count($sets));

        $sets = $apiService->getSetsByName('Ninth');
        $this->assertIsArray($sets);
        $this->assertGreaterThan(0, count($sets));
    }

    public function testGetAllSets()
    {
        self::bootKernel();

        $container = self::$container;

        $apiService = $container->get(ScryfallApiService::class);
        $sets = $apiService->getAllSets();
        $this->assertIsArray($sets);
        $this->assertGreaterThan(0, count($sets));
    }

}