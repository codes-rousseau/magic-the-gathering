<?php

namespace App\Form;

use App\Entity\Color;
use App\Repository\ColorRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['required' => false, 'attr' => ['placeholder' => 'Nom']])
            ->add('colors', EntityType::class, [
                'class' => Color::class,
                'query_builder' => function (ColorRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('color');
                },
                'multiple' => true,
                'expanded' => false,
                'choice_label' => 'name',
                'required' => false
            ])
            ->add('type', TextType::class, ['required' => false, 'attr' => ['placeholder' => 'Type']])
            ->add('submit', SubmitType::class, ['label' => 'Filtrer']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
