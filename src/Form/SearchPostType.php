<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Region;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // formulaire de recherche en get (pas en post)
            ->setMethod('GET')
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Titre',
                    // les champs de recherche ne sont pas obligatoires
                    'required' => false
                ]
            )
            ->add(
                'category',
                EntityType::class,
                [
                    'label' => 'Catégorie',
                    'class' => Category::class,
                    'choice_label' => 'name',
                    //placeholder pour avoir une categorie vide
                    'placeholder' => 'Choisissez une catégorie',
                    'required' => false
                ]
            )
            ->add(
                'region',
                EntityType::class,
                [
                    'label' => 'Région',
                    'class' => Region::class,
                    'choice_label' => 'publicName',
                    //placeholder pour avoir une categorie vide
                    'placeholder' => 'Choisissez votre région',
                    'required' => false
                ]
            )
            ->add(
                'sortPublicationDate',
                ChoiceType::class,
                [
                    'label' => 'Tri par date',
                    'choices' => [
                        'décroissante' => 'DESC',
                        'croissante' => 'ASC'
                    ]
                    //placeholder pour avoir une categorie vide


                ]
            )

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => false,
        ]);
    }
}
