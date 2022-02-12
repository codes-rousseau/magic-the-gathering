<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Color;
use App\Repository\ColorRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('card_name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Optional(),
                    new Assert\Length(['min' => 2]),
                ],
            ])
            ->add('card_type', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Optional(),
                    new Assert\Length(['min' => 2]),
                ],
            ])
            ->add('card_colors', EntityType::class, [
                'class' => Color::class,
                'query_builder' => function (ColorRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('color');
                },
                'expanded' => false,
                'multiple' => true,
                'choice_label' => 'name',
                'required' => false,
                'constraints' => [
                    new Assert\Optional(),
                ],
            ])
        ;
    }
}
