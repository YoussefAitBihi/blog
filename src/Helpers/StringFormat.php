<?php

namespace App\Helpers;

class StringFormat {

   /**
    * Permet d'extraire un paragraphe
    *
    * @return string|null
    */
   public static function extractString(string $chaine, int $limit = 80): ?string 
   {
      if (mb_strlen($chaine) <= $limit):
         return $chaine;
      endif;

      $pos = mb_strpos($chaine, ' ', $limit);

      if ($pos === false) {
         return mb_substr($chaine, 0, $limit).'.';
      }

      return mb_substr($chaine, 0, $pos).'.';
   }

}