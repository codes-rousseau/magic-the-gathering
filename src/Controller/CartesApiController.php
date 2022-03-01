<?php

namespace App\Controller;

use App\Entity\CardCollection;
use App\Service\ScryfallService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartesApiController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    private $scryfallService;


    public function __construct(ScryfallService $scryfallService)
    {
        $this->scryfallService = $scryfallService;

    }

    /**
     * @return Response
     * @Route("test")
     */
    public function test()
    {


        $res = $this->scryfallService->getAllCards();
        dump($res);
        return new Response('<body>toto</body>');
    }
}