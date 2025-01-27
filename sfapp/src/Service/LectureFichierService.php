<?php

namespace App\Service;

class LectureFichierService
{
    public function readFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Le fichier n'existe pas : $filePath");
        }

        return file_get_contents($filePath);
    }
}
