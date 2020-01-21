<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
            // nom du champ qui correspond à attribut
            // ds l'entité Category
                'name',
                //type de champ du form (input text)
                TextType::class,
                //tableau d'option ou en precise le libellés
                [
                    //contenu de la la balise <label>
                    'label' => 'Nom'
                ]
            )
            ->add(

                'logo', FileType::class, array(
                    'data_class' => null,
                    'label' => 'Logo',
                    'required' => false
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
