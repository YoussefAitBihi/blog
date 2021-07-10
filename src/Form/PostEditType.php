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

class PostEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title', 
                TextType::class, 
                FormOptions::formOptions('Modifier le titre')
            )
            ->add(
                'introdution', 
                TextType::class, 
                FormOptions::formOptions("Modifier l'introduction")
            )
            ->add(
                'description', 
                TextareaType::class, 
                FormOptions::formOptions('Modifier la descriptiokn')
            )
            ->add(
                'image',
                FileType::class,
                FormOptions::formFileOptions(false, false, "Modifier l'image", ['image/jpeg', 'image/png'], "2M", "Format invalide! Veuillez choisir soit JPEG soit PNG", "L'image ne doit pas dépasser 2Mb")
            )
            ->add(
                'video', 
                FileType::class, 
                FormOptions::formFileOptions(false, false, "Modifier la vidéo", ['video/mp4', 'video/x-msvideo'], "10M", "Format invalide ! Veuillez choisir soit MP4 soit AVI", "La vidéo ne doit pas dépasser 10Mb")
            )
            ->add('images', CollectionType::class, [
                'entry_type'        => ImageType::class,
                'allow_add'         => true,
                'allow_delete'      => true,
                'error_bubbling'    => true
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
