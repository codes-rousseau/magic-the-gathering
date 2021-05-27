<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class UploadImgService
{

    private $params;
    private $urlGenerator;


    public function __construct(ParameterBagInterface $params, UrlGeneratorInterface $urlGenerator)
    {
        $this->params = $params;
        $this->urlGenerator = $urlGenerator;

    }

    /*
     * Upload image in server and generate unique file name
     */
    public function uploadAsset($url)
    {

        $newFilename = uniqid() . '.png';
        $img = $this->params->get('assets_directory').$newFilename;
        file_put_contents($img, file_get_contents($url));


        return $this->params->get('assets_directory_relative').$newFilename;

    }






}