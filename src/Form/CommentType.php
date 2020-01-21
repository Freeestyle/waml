<?php


namespace App\Form;


use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
            // nom du champ qui va correspondre au nom de l'attribut dans la classe
            // dans l'entité Comment
                'content',
                // type de champ de formulaire (input text)
                TextareaType::class,
                // https://symfony.com/doc/current/reference/forms/types.html
                // 3e paramètre : tableau d'option
                [
                    // contenu de la balise label
                    'label' => 'Votre commentaire'
                ]

            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }

}