<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Region;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Titre'
                ])
            ->add(
                'content',
                TextareaType::class,
                [

                ])
            ->add(

                'image', FileType::class, array(
                    'data_class' => null,
                    'label' => 'Photo 1',
                    'required' => false
                )
            )
            ->add(

                'image2', FileType::class, array(
                    'data_class' => null,
                    'label' => 'Photo2',
                    'required' => false
                )
            )
            ->add(

                'image3', FileType::class, array(
                    'data_class' => null,
                    'label' => 'Photo3',
                    'required' => false
                )
            )
            ->add(
                'category',
                EntityType::class,

                [

                    'class' => Category::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Choisissez une catégorie'
                ])
            ->add(
                'region',
                EntityType::class,

                [

                    'class' => Region::class,
                    'choice_label' => 'public_name',
                    'placeholder' => 'Choisissez une region'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
