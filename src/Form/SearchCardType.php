<?php

namespace App\Form;

use App\Classes\Search;
use App\Entity\Color;
use App\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Nom',
            ])
            ->add('color', EntityType::class, [
                'required' => false,
                'label' => 'Couleur',
                'choice_label' => 'label',
                'class' => Color::class,
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('type', EntityType::class, [
                'required' => false,
                'label' => 'Type',
                'choice_label' => 'label',
                'class' => Type::class,
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'crsf_protection' => false,
        ]);
    }
}
