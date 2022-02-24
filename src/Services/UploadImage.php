<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UploadImage
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->params = $parameterBag;
    }

    public function upload( string $directory, string $imageName, string $uri ): string
    {
        $path = $this->params->get('kernel.project_dir') . '/public' . '/' . $directory . '/';
        mkdir($path, 0777, true);
        file_put_contents($path . $imageName, file_get_contents($uri));
        return '/' . $directory . '/' . $imageName;
    }
}