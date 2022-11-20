<?php

namespace App\Form;

use App\Entity\Card;
use App\Repository\ColorRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchCardsType extends AbstractType
{
    private ColorRepository $colorsRepo;

    public function __construct(ColorRepository $colorsRepo) {
        $this->colorsRepo = $colorsRepo;

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'required' => false,
                'row_attr' => [
                    'class' => 'input-group',
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('color', null, [
                'required' => false,
                'row_attr' => [
                    'class' => 'input-group',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('type', null, [
                'required' => false,
                'row_attr' => [
                    'class' => 'input-group',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
                'row_attr' => [
                    'class' => 'align-self-center',
                ],
                'label' => 'Search'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Card::class,
            'attr' => [
                'class' => 'd-flex'
            ]
        ]);
    }
}
