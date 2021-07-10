<?php

namespace App\Helpers;

use Symfony\Component\Validator\Constraints\File;

class FormOptions {

   /**
    * Permet de configurer le formulaire
    *
    * @param string $placeholder contenant le placeholder
    * @return array|null
    */
   public static function formOptions(string $placeholder): ?array 
   {
      return array(
         'label' => false,
         'attr' => [
            'placeholder' => $placeholder
         ]
      );
   }

   /**
    * Sert à configuer un champ de type File
    *
    * @param boolean $label
    * @param string $place
    * @param string $size
    * @param array $types
    * @param string $sizeMessage
    * @param string $typesMessage
    * @return array|null
    */
   public static function formFileOptions(
      bool $label = false,
      bool $required = true,
      string $place,
      array $types,
      string $size,
      string $typesMessage,
      string $sizeMessage
   ): ?array {

      return [
         'label'     => $label,
         'required'  => $required,
         'attr'      => [
            'placeholder' => $place,
         ],
         'mapped'       => false,
         'constraints'  => [
             new File([
               'mimeTypes'    => $types,
               'maxSize'      => $size,
               'mimeTypesMessage'   => $typesMessage,
               'maxSizeMessage'     => $sizeMessage
            ])
         ]
      ];
   }

}