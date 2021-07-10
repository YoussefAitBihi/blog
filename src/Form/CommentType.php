<?php

namespace App\Form;

use App\Entity\Comment;
use App\Helpers\FormOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content', 
                TextareaType::class, 
                FormOptions::formOptions('Laisser un commentaire')
            )
            ->add(
                'rating', 
                IntegerType::class, [
                    'label' => false,
                    'attr'  => [
                        'placeholder'   => '5 étoiles si vous-en a plu',
                        'min'           => 1,
                        'max'           => 5,
                        'step'          => 1
                ]    
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
