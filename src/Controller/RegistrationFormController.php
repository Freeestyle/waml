<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationFormController extends AbstractController
{
    public function renderForm(Request $request)
    {
        // Pour générer un formulaire qui sera utilisé dans la modal inscription
        $user = new User();
        $formRegister = $this->createForm(RegistrationFormType::class, $user);
        $formRegister->handleRequest($request);
        return $this->render('registration/registration_modal.html.twig', [
            'registrationForm' => $formRegister->createView(),
        ]);
    }
}
