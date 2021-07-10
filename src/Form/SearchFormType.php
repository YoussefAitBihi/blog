<?php

namespace App\Form;

use App\Search\SearchPost;
use App\Helpers\FormOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SearchType;

class SearchFormType extends AbstractType 
{

   public function buildForm(FormBuilderInterface $builder, array $options)
   {
      $builder
         ->add('query', SearchType::class, FormOptions::formOptions("Rechercher ce que vous vouliez"));
   }

   public function configureOptions(OptionsResolver $resolver)
   {
      $resolver->setDefaults([
         'data_class'      => SearchPost::class,
         'method'          => 'GET',
         'csrf_protection' => false
      ]);   
   }

   public function getBlockPrefix()
   {
      return '';
   }
}