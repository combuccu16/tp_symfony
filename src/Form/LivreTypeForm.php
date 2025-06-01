<?php

namespace App\Form;

use App\Entity\Livre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivreTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'attr' => ['placeholder' => 'Titre du livre', 'class' => 'form-control'],
                'label' => 'Titre *'
            ])
            ->add('description', TextType::class, [
                'attr' => ['placeholder' => 'Résumé ou contenu du livre', 'class' => 'form-control'],
                'label' => 'Description *'
            ])
            ->add('author', TextType::class, [
                'attr' => ['placeholder' => 'Nom de l’auteur', 'class' => 'form-control'],
                'label' => 'Auteur *'
            ])
            ->add('price', IntegerType::class, [
    'label' => 'Prix (€)',
    'required' => true,
    'attr' => [
        'placeholder' => 'Ex : 10',
        'min' => 0
    ],
])
            ->add('stock', IntegerType::class, [
                'attr' => ['min' => 0, 'class' => 'form-control'],
                'label' => 'Stock *'
            ])
            ->add('ISBN', IntegerType::class, [
                'attr' => ['placeholder' => 'ISBN unique', 'class' => 'form-control'],
                'label' => 'ISBN *'
            ])
            ->add('publicationDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de publication *',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livre::class,
        ]);
    }
}
