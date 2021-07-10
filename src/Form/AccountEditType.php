<?php

namespace App\Form;

use App\Entity\User;
use App\Helpers\FormOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AccountEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                FormOptions::formOptions('Modifier le premier nom'))
            ->add(
                'lastName',
                TextType::class,
                FormOptions::formOptions('Modifier le deuxième nom'))
            ->add(
                'email',
                EmailType::class,
                FormOptions::formOptions('Modifier l\'email'))
            ->add(
                'description',
                TextareaType::class,
                FormOptions::formOptions('Modifier la description'))
            ->add(
                'picture', 
                FileType::class, 
                FormOptions::formFileOptions(false, false, "Modifier la photo de picture", ["image/jpeg", "image/png"], "2M", "Format invalide! Veuillez choisir un format valide", "Vous ne pouvez pas dépasser 2Mb")
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['user_edit']
        ]);
    }
}
