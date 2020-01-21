<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="inscription")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response
    {
        // si le User est déjà connecté
        if (!empty($this->getUser())) {
            // redirection vers l'index pour securiser l'acces à la page inscription
            return $this->redirectToRoute('app_index_index');

        } else {
            // autrement, si aucun User est connecté, la page inscription est accesible
            $user = new User();
            $form = $this->createForm(
                RegistrationFormType::class,
                $user,
                array('validation_groups' => array('registration', 'Default'))
            );
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    // encode the plain password
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            //on va recuperer la methode qu'on a crée pour le mdp en clair
                            $user->getPlainPassword()
                        )
                    );

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash(
                        'success',
                        'Votre compte est créé, merci de vous connecter'
                    );

                    // j'injecte la route pour la home page
                    return $this->redirectToRoute('app_index_index');
                } else {
                    return new Response('errorRegistration');
                }
            }
            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route("/connexion")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // si le User est déjà connecté
        if (!empty($this->getUser())) {
            // redirection vers l'index si la deniere page visité est la connection (en cas d'un f5)
            return $this->redirectToRoute('app_index_index');

            // autrement, apres connexion, le User est redirigé
            // vers la deniere page visité (voir template twig)
        } else {

            // traitement du formulaire par Security
            // on recupere une methode de la classe en argument
            $error = $authenticationUtils->getLastAuthenticationError();
            $lastUsername = $authenticationUtils->getLastUsername();

            if (!empty($error)) {
                 return new Response('wrong');
            }

            return $this->render(
                'registration/login.html.twig',
                [
                    'last_username' => $lastUsername
                ]
            );
        }
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param Swift_Mailer $swift_Mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @return Response
     * @Route("/oublie-mot-de-passe", name="app_forgotten_password", methods="GET|POST")
     */
    public function forgottenPassword(Request $request, UserPasswordEncoderInterface $userPasswordEncoder, Swift_Mailer $swift_Mailer, TokenGeneratorInterface $tokenGenerator): Response
    {
        // Récupération et vérification si l'email saisi existe en BDD
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user === null) {
                $this->addFlash('danger', 'Email Inconnu, recommence');
                return $this->redirectToRoute('app_forgotten_password');
            }

            // Génération d'un token aleatoire et enregistrement en BDD dans le champ reset_token
            $token = $tokenGenerator->generateToken();
            try {
                $user->setResetToken($token);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('index');
            }
            // On injecte le token dans l'url qui sera envoyé par mail à l'utilisateur
            // qui permettra de vérifier si les deux tokens correspondent (celui en BDD et dans l'url)
            $url = $this->generateUrl('app_reset_password', array('token' => $token),
                UrlGeneratorInterface::ABSOLUTE_URL);
            // Titre et sous-titres du mail
            $message = (new Swift_Message('Oubli de mot de passe - Réinisialisation'))
                ->setFrom(array('ollivier.johan92@gmail.com' => 'Projet-defoulement'))
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView( // on recupere le template qui sera affiché dans le mail
                        'registration/emails/resetPasswordMail.html.twig',
                        [
                            'user' => $user,
                            'url' => $url
                        ]
                    ),
                    'text/html'
                );
            $swift_Mailer->send($message);
            $this->addFlash('notice', 'Mail envoyé !');
            return $this->redirectToRoute('app_registration_login');
        }
        return $this->render('registration/forgottenPassword.html.twig');
    }


    /**
     * @Route("/reinitialiser-mot-de-passe/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        // Récuperation du token en BDD dans le champ reset_token de la table user
        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneByResetToken($token);
            // Si les tokens ne correspondent pas, envoie d'une erreur
            if ($user === null) {
                $this->addFlash('danger', 'Mot de passe non reconnu');
                return $this->redirectToRoute('app_index_index');
            }
            // Si les tokens correspondent, on sette à NULL le token existant en BDD
            $user->setResetToken(null);
            // On remplace le mdp par celui saisie par l'utilisateur
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();
            $this->addFlash('notice', 'Mot de passe mis à jour !');
            return $this->redirectToRoute('app_index_index');
        } else {
            return $this->render('registration/resetPassword.html.twig', ['token' => $token]);
        }
    }


    /**
     * il suffit que la route existe et qu'elle soit configurée ds securit.yaml
     * pour que la déconnexion soit gérée dar le composerSecurity
     *
     * @Route("/deconnexion")
     */
    public function logout()
    {
        // la methode pour rester vide
    }
}
