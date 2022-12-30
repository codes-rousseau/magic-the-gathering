<?php

namespace App\Form;

use App\Entity\Card;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('picture')
            ->add('type')
            ->add('color')
            ->add('description')
            ->add('artistName')
            ->add('Collection')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Card::class,
        ]);
    }
}
