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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RegisterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName', 
                TextType::class, 
                FormOptions::formOptions('Premier nom')
            )
            ->add(
                'lastName', 
                TextType::class, 
                FormOptions::formOptions('Deuxième nom')
            )
            ->add(
                'email', 
                EmailType::class, 
                FormOptions::formOptions('Adresse e-mail')
            )
            ->add(
                'hash', 
                PasswordType::class, 
                FormOptions::formOptions('Mot de passe')
            )
            ->add(
                'confirmPassword', 
                PasswordType::class, 
                FormOptions::formOptions('Confirmation de mot de passe')
            )
            ->add(
                'description', 
                TextareaType::class, 
                FormOptions::formOptions('Description')
            )
            ->add(
                'picture', 
                FileType::class, 
                FormOptions::formFileOptions(false, true, "Une photo de votre profil", ['image/jpeg', 'image/png'], "2M", "Format invalide ! Veuillez choisir soit JPEG soit PNG", "L'image ne doit pas dépasser 2Mb")
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
