<?php

namespace tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CardsControllerTest extends WebTestCase
{

    public function testErrorIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/cards');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/1/cards');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}