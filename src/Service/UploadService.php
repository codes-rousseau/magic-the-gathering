<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UploadService
{
    private const BASE_UPLOAD_DIR = 'public\\img';

    public function uploadFile($uri, $dir, $name): String {

        $this->createDir($dir);

        if(copy( $uri, self::BASE_UPLOAD_DIR  . '\\' . $dir  . '\\' .  $name . '.svg')) {
            return self::BASE_UPLOAD_DIR  . '\\' . $dir  . '\\' . $name . '.svg';
        } else {
            return "";
        }
    }

    private function createDir( $dir ) {
        $fs = new Filesystem();
        
        if( !$fs->exists(self::BASE_UPLOAD_DIR) ) {
            $fs->mkdir(self::BASE_UPLOAD_DIR, 0777);
        }

        if( !$fs->exists(self::BASE_UPLOAD_DIR . '\\' . $dir) ) {
            $fs->mkdir(self::BASE_UPLOAD_DIR . '\\' . $dir, 0777);
        }
    }
}