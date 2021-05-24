<?php


namespace App\Form;


use App\Classe\Search;
use App\Entity\Color;
use App\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' =>  false,
                'attr' => [
                    'placeholder' => 'Nom de la carte'
                ]
            ])
            ->add('type', EntityType::class, [
                'required'=> false,
                'class'=> Type::class,
                'placeholder' => 'Recherche par Type',
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('color', EntityType::class, [
                'required'=> false,
                'class'=> Color::class,
                'placeholder' => 'Recherche par Couleur',
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('submit', SubmitType::class,[
                'label'=> 'Rechercher'
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'method' => 'GET',
            'crsf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

}