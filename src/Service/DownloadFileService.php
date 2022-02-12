<?php

declare(strict_types=1);

namespace App\Service;

use function Humbug\get_contents;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mime\MimeTypes;

class DownloadFileService
{
    private string $publicDirectory;

    /**
     * Taille maximum des fichiers à télécharger exprimé en octet.
     */
    private int $maxDownloadFileSize;

    public function __construct(
        string $publicDirectory,
        int $maxDownloadFileSize
    ) {
        $this->publicDirectory = $publicDirectory;
        $this->maxDownloadFileSize = $maxDownloadFileSize;
    }

    /**
     * Télécharge un fichier dans le répertoire temporaire du système à partir d'une URL HTTPS uniquement.
     *
     * @param string      $url  URL du fichier à télécharger
     * @param string|null $name nom du fichier temporaire
     *
     * @return string chemin vers le fichier temporaire
     */
    public function downloadFileByHttpsUrl(string $url, string $name = null): string
    {
        if (!$resource = fopen($url, 'r')) {
            throw new FileException(sprintf('Unable to open file located at this address: %s.', $url));
        }

        fclose($resource);

        // Nous vérifions que le fichier n'est pas trop volumineux pour éviter de saturer l'espace disque.
        $size = $this->getSizeFileByUrl($url);
        if ($size > $this->maxDownloadFileSize) {
            throw new FileException(sprintf('The file size should not be greater than : %d bytes.', $this->maxDownloadFileSize));
        }

        $path = (null === $name)
            ? tempnam(sys_get_temp_dir(), 'download_')
            : sys_get_temp_dir().'/'.$name;

        if (false === $path) {
            throw new FileException(sprintf('Unable to create a file into the "%s" directory.', $path));
        }

        if (false === $content = get_contents($url)) {
            throw new FileException(sprintf('Unable to retrieve file contents for this URL "%s".', $url));
        }

        if (false === file_put_contents($path, $content)) {
            throw new FileException(sprintf('Unable to write temporary file "%s".', $path));
        }

        $mimeTypes = new MimeTypes();
        $mimeType = $mimeTypes->guessMimeType($path);
        if (null !== $extension = (MimeTypes::getDefault()->getExtensions($mimeType)[0] ?? null)) {
            $oldPath = $path;
            $path = sprintf('%s.%s', $path, $extension);

            if (false === rename($oldPath, $path)) {
                return $oldPath;
            }
        }

        return $path;
    }

    /**
     * Récupère la taille du fichier via les entêtes à partir d'une URL.
     */
    public function getSizeFileByUrl(string $url): ?int
    {
        $context = stream_context_create(['http' => ['method' => 'HEAD']]);
        // Change parameter type associative in PHP 8.0 :
        // https://www.php.net/manual/fr/function.get-headers.php
        $headers = get_headers($url, 1, $context);

        if (is_array($headers) && isset($headers['Content-Length'])) {
            return intval($headers['Content-Length']);
        }

        return null;
    }

    /**
     * Déplace un fichier vers un répertoire public Web sans changer le nom du fichier.
     * Si le répertoire n'existe pas, nous le créons de manière récursive.
     *
     * @param string $from              chemin actuel du fichier
     * @param string $relativeDirectory répertoire où déplacer le fichier
     *
     * @return false|string chemin relative vers le nouveau emplacement du fichier public
     */
    public function moveFileInPublicDirectory(string $from, string $relativeDirectory)
    {
        $absoluteDirectory = realpath($this->publicDirectory.'/'.$relativeDirectory);
        $this->assertPathIsInPublicDirectory($absoluteDirectory);

        if (!is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0777, true);
        }

        $absoluteFile = $absoluteDirectory.'/'.basename($from);
        if (true === rename($from, $absoluteFile)) {
            return substr($absoluteFile, strlen($this->publicDirectory) + 1);
        }

        return false;
    }

    /**
     * Supprimer un fichier qui se situe dans le répertoire public.
     */
    public function removeFileInPublicDirectory(string $relativeFile): bool
    {
        $absoluteFile = realpath(dirname($this->publicDirectory.'/'.$relativeFile)).'/'.basename($relativeFile);
        $this->assertPathIsInPublicDirectory($absoluteFile);

        if (file_exists($absoluteFile)) {
            unlink($absoluteFile);

            return true;
        }

        return false;
    }

    /**
     * Vérifie qu'un fichier/répertoire en chemin absolu est dans le répertoire public.
     */
    private function assertPathIsInPublicDirectory(string $absolutePath): void
    {
        if (0 !== strpos($absolutePath, $this->publicDirectory)) {
            throw new FileException('Please enter a relative path.');
        }
    }
}
