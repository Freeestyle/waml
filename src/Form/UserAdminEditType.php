<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAdminEditType extends UserEditType
{
//    ce formulaire est dédié à l'administrateur (et hérite du formulaire utilisateur (UserEditType))
//     il permet que l'utilisateur n'ait pas accès au choix de son rôle (Admin ou Membre)

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add(
                'role',
//                ChoiceType pour permettre l'affichage d'un select (et ajout de choice_label ci-dessous)
                ChoiceType::class,
                [
                    'label' => 'Role',
                    'choices' => [
                        'Admin' => 'ROLE_ADMIN',
                        'Membre' => 'ROLE_USER'
                    ]
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
