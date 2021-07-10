<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * L'uploader
 */
class Uploader {

   /**
    * Permet d'uploader et de retourner le nouveau nom du fichier
    *
    * @param UploadedFile $file
    * @param string $directory
    * @param string $errorMessage
    * @return string|null
    */
   public function upload(UploadedFile $file, string $directory, string $errorMessage = "Ce fichier n'a pas été uploadé! Réssayer ultérieurement"): ?string 
   {
      if (!$file):
         throw new FileException("L'insertion de l'image ou de la vidéo est une obligation.");
      endif;

      // Nom original
      $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
      // Extension
      $extension = $file->guessClientExtension();
      // Nouveau nom
      $newFilename = $originalFilename.'-'.uniqid().'.'.$extension;

      $moved = $file->move($directory, $newFilename);

      // L'exception
      if (!$moved):
         throw new FileException($errorMessage);
      endif;

      return $newFilename;
   }

}