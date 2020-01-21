<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'lastname',
                TextType::class,
                [
                    'attr' => ['placeholder' => 'Nom'],
                ]
            )
            ->add(
                'firstname',
                TextType::class,
                [
                    'attr' => ['placeholder' => 'Prénom']
                ]
            )
            ->add('email',
                EmailType::class,
                [
                    'attr' => ['placeholder' => 'email']
                ]
            )
            ->add(
                'plainPassword',
                //genere 2 champs de saisie qui doinvent avoir la meme valeur rentrée
                RepeatedType::class,
                [
                    // ... type de champ password
                    'type' => PasswordType::class,
                    //options du 1er champ
                    'first_options' => [
                        'attr' => [
                            'placeholder' => 'Mot de passe'
                        ]
                    ],
                    //options du second champ
                    'second_options' => [
                        'attr' => [
                            'placeholder' => 'Confirmation du mot de passe'
                        ]
                    ],
                    //si les 2 champs n'ont pas la meme valeur
                    'invalid_message' => 'La confirmation ne correspond pas au mot de passe'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
