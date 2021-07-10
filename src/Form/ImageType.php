<?php

namespace App\Form;

use App\Entity\Image;
use App\Helpers\FormOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'caption', 
                TextType::class, 
                FormOptions::formOptions('Sous titre')
            )
            ->add(
                'img', 
                FileType::class,  
                FormOptions::formFileOptions(false, true, "L'image de carousel", ["image/jpeg", "image/png"], "2M", "Format invalide! Veuillez choisir soit jpeg soit png", "L'image ne doit pas dépasser 2Mb")
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
