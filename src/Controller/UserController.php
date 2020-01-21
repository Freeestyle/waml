<?php

namespace App\Controller;

/*use App\Entity\Favorite;*/

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Form\UserEditType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * Class UserController
 * @package App\Controller
 * @Route("/")
 */
class UserController extends AbstractController
{
    /**
     *
     * @Route("/profil")
     * @param UserRepository $userRepository
     * @param PostRepository $postRepository
     * @param User $users
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profil(PostRepository $postRepository)
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $posts = $postRepository->findBy(['author' => $userId], ['publicationDate' => 'DESC']);

        return $this->render('/profil/index.html.twig',
            [
                'user' => $user,
                'original_photo' => $user->getPhoto(),
                'posts' => $posts,
                'favorites' => $user->getFavorites()
            ]);
    }


    /**
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @Route("/profil/edition")
     *
     */
    public function edit(Request $request, EntityManagerInterface $entityManager)
    {
        $originalPhoto = null;
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_registration_login');
        }

        if (!is_null($user->getPhoto())) {
            $originalPhoto = $user->getPhoto();
            $user->setPhoto(
                new File($this->getParameter('upload_dir') . $originalPhoto)
            );
        }
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (@$form->isValid()) {
                /**
                 * @var UploadedFile $photo
                 */
                $photo = $form->get('photo')->getData();
                if (!is_null($photo)) {
                    $filename = uniqid() . '.' . $photo->guessExtension();
                    $photo->move($this->getParameter('upload_dir'),
                        $filename
                    );
                    $user->setPhoto($filename);
                    if (!is_null($originalPhoto)) {
                        unlink($this->getParameter('upload_dir') . $originalPhoto);
                    }
                } else {
                    $user->setPhoto($originalPhoto);
                }
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Les modifications ont été effectuées');
                return $this->redirectToRoute('app_user_profil');
            } else {
                // message de confirmation
                $this->addFlash('danger', 'Le formulaire contient des erreurs');
            }
        }
        return $this->render('/profil/edit.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
                'original_image' => $originalPhoto
            ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/profil/reset-password")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $form = $this->createForm(ResetPasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupere le mot de passe (non crypté) du formulaire saisi par l'utilisateur
            $oldPassword = $request->request->get('reset_password')['oldPassword'];

            // Verification si l'ancien mot de passe (crypté) est le bien le même que celui enregistré en BDD
            if ($passwordEncoder->isPasswordValid($user, $oldPassword)) { // Return "true" si le mdp est identique
                // Encodage du nouveau mdp et enregistrement en BDD dans la table User
                $newEncodedPassword = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($newEncodedPassword);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('notice', 'Votre mot de passe à bien été changé !');
                return $this->redirectToRoute('app_user_profil');
            } else {
                $form->addError(new FormError('Ancien mot de passe incorrect'));
            }
        }
        return $this->render('profil/resetPassword.html.twig',
            [
                'form' => $form->createView(),
            ]);
    }


    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/profile/{id}", defaults={"id":null}, requirements={"id": "\d+"})
     */
    public function profileVisit(
        $id,
        UserRepository $userRepository)
    {
        if (!empty($id)) {
            $user = $userRepository->find($id);
        }
        return $this->render('profil/profilview.html.twig',
            [
                'user' => $user,
                'original_photo' => $user->getPhoto(),
                'posts' => $user->getPosts()
            ]);
    }


}//fin de la class UserController
