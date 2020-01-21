<?php


namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserAdminEditType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class UserController
 * @package App\Controller\Admin
 * @Route("/user")
 */
class UserController extends AbstractController

{
    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/")
     */
    public function index(UserRepository $userRepository)
    {
        // Requete pour trouver tous les Utilisateurs en BDD
        $users = $userRepository->findAll();
        return $this->render('Admin/User/index.html.twig',
            [
                'users' => $users
            ]);

    }

    /**
     * @Route("/suppression/{id}", requirements={"id": "\d+"})
     * @param EntityManagerInterface $entityManager
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(EntityManagerInterface $entityManager, User $user)
    {
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'l\'utilisateur a été supprimé');
        return $this->redirectToRoute('app_admin_user_index');
    }


    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @Route("/edition/{id}", defaults={"id":null}, requirements={"id":"\d+"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $originalPhoto = null;

        $user = $entityManager->find(User::class, $id);

        // Erreur 404 si l'id n'existe pas
        if (is_null($user)) {
            throw new NotFoundHttpException();

        }
        // S'il y a une photo, on sette l'image avec l'objet File dans l'emplacement de l'image pour le traitement via le formulaire.
        if (!is_null($user->getPhoto())) {
            $originalPhoto = $user->getPhoto();

            $user->setPhoto(
                new File($this->getParameter('upload_dir') . $originalPhoto)
            );
        }

        // Création d'un formulaire relié à l'id USER

        $form = $this->createForm(UserAdminEditType::class, $user);
        // Analyse de la requête.
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                /** @var UploadedFile $photo */
                $photo = $form->get('photo')->getData();

                // S'il y a une image d'uploadée.
                if (!is_null($photo)) {
                    $filename = uniqid() . '.' . $photo->guessExtension();
                    $photo->move(
                    // On cible le repertoire où la photo sera deplacée:
                        $this->getParameter('upload_dir'),
                        // Sous quel nom:
                        $filename
                    );

                    // On sette l'attribut image (setPhoto) de l'user avec le nom du fichier ($filename) pour l'enregistrement en BDD.
                    $user->setPhoto($filename);

                    // Si on upload une nouvelle photo, on supprime (unlink) l'ancienne photo.
                    if (!is_null($originalPhoto)) {
                        unlink($this->getParameter('upload_dir') . $originalPhoto);
                    }
                } else {
                    // Si on upload pas de nouvelle photo, on (re)sette l'image avec l'ancienne photo.
                    $user->setPhoto($originalPhoto);
                }

                // Enregistrement en BDD
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Les modifications ont été effectuées');
                return $this->redirectToRoute('app_admin_user_index');
            } else {
                // message de confirmation
                $this->addFlash('danger', 'Le formulaire contient des erreurs');
            }
        }

        return $this->render(
            'Admin/User/edit.html.twig',
            [
                'form' => $form->createView(),
                'original_image' => $originalPhoto,
                'users' => $user
            ]
        );
    }

}