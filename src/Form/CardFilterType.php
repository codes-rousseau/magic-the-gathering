<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       // dd($options);
        $builder
            ->add('type', ChoiceType::class, ['label' => 'Type', 'attr' => ['class' => 'form-control'], 'required' => false, 'choices' => $options['options']['type']])
            ->add('color', ChoiceType::class, ['label' => 'Couleur', 'attr' => ['class' => 'form-control'], 'required' => false, 'choices' => $options['options']['color']])
            ->add('name', TextType::class, ['label' => 'Nom', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'form-control  mt-4 mb-4'], 'label' => 'Filtrer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => false,
            'options' => []
        ]);
        $resolver->setAllowedTypes('options', 'array');
    }
}