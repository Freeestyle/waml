<?php

namespace App\Controller;


use App\Form\ContactType;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ContactController
 * @package App\Controller
 * @Route("/contact")
 */
class ContactController extends AbstractController
{
    /**
     * @Route("/")
     * @param Swift_Mailer $swift_Mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contact(Request $request, Swift_Mailer $swift_Mailer)
    {

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $contactFormData = $form->getData();

                $message = (new Swift_Message('Test envoie mail'))
                    ->setFrom($contactFormData['email'])
                    ->setTo('uppehogaz-7201@yopmail.com')
                    ->setBody(
                        $contactFormData['message'],
                        'text/plain');
                $swift_Mailer->send($message);
                $this->addFlash('success', 'message envoyé');
                $this->redirectToRoute('app_contact_contact');
            }
        }
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
