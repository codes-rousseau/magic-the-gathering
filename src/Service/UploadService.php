<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UploadService
{
    private const BASE_UPLOAD_DIR = 'img';
    private const PREFIXE = 'public';

    public function uploadFile(String $uri, String $dir , String $name): String {

        $this->createDir($dir);

        if(copy( $uri, self::PREFIXE . '\\' . self::BASE_UPLOAD_DIR  . '\\' . $dir  . '\\' .  $name )) {
            return self::BASE_UPLOAD_DIR  . '\\' . $dir  . '\\' . $name;
        } else {
            return "";
        }
    }

    private function createDir( String $dir ) {
        $fs = new Filesystem();

        if( !$fs->exists(self::PREFIXE . '\\' . self::BASE_UPLOAD_DIR) ) {
            $fs->mkdir(self::PREFIXE . '\\' . self::BASE_UPLOAD_DIR, 0777);
        }

        if( !$fs->exists(self::PREFIXE . '\\' . self::BASE_UPLOAD_DIR . '\\' . $dir) ) {
            $fs->mkdir(self::PREFIXE . '\\' . self::BASE_UPLOAD_DIR . '\\' . $dir, 0777);
        }
    }
}