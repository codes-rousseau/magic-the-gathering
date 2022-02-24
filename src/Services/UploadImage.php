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
        file_put_contents($this->params->get('kernel.project_dir') . '/public' . '/' . $directory . '/' . $imageName, file_get_contents($uri));
        return '/' . $directory . '/' . $imageName;
    }
}