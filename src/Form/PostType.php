<?php

namespace App\Form;

use App\Entity\Post;
use App\Form\ImageType;
use App\Helpers\FormOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PostType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title', 
                TextType::class, 
                FormOptions::formOptions('Nom de poste')
            )
            ->add(
                'image', 
                FileType::class, 
                FormOptions::formFileOptions(false, true, "Image de couverture", ['image/jpeg', 'image/png'], '2M', "Format invalide ! Veuillez choisir soit format jpeg, jpg ou png", "L'image ne doit pas dépasser 2Mb")
            )
            ->add(
                'video', 
                FileType::class, 
                FormOptions::formFileOptions(false, true, "La vidéo", ['video/mp4', 'video/x-msvideo'], '10M', "Format invalide! Veuillez choisir soit format mp4 ou avi", "La vidéo doit-faire inférieur ou égale 10Mb")
            )
            ->add(
                'introdution', 
                TextType::class, 
                FormOptions::formOptions('Une courte introduction')
            )
            ->add(
                'description', 
                TextareaType::class, 
                FormOptions::formOptions('Une longue description')
            )
            ->add(
                'images', 
                CollectionType::class, [
               'entry_type' => ImageType::class,
               'allow_add'  => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class
        ]);
    }
}
