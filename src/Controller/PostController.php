<?php


namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserFavoritesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PostController
 * @package App\Controller\Admin
 *
 * @Route("/annonce")
 *
 *
 */
class PostController extends AbstractController
{


    // *******************************************************************************
    //                  FORMULAIRE AJOUT et MODIFICATION ANNONCE
    // *******************************************************************************
    /**
     *
     *
     * @Route("/modification/{id}", defaults={"id":null}, requirements={"id":"\d+"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, EntityManagerInterface $manager, $id)
    {
        $originalImage = null;
        $originalImage2 = null;
        $originalImage3 = null;

        if (is_null($id)) { // création d'une annonce
            $post = new Post();

            // NB : date de publication settée dans le constructeur de l'entity Post

        } else { // modification de l'annonce
            $post = $manager->find(Post::class, $id);

            //On jette une 404 si l'id reçu n'est pas en BDD
            if (is_null($post)) {
                throw new NotFoundHttpException();
            }

            // pour éviter qu'on force l'URL pour modifier une annonce
            // Si l'auteur du post n'est pas l'user connecté et s'il n'est pas admin, on le renvoit en homepage
            if ($post->getAuthor() != $this->getUser() && $this->getUser()->getRole() != 'ROLE_ADMIN') {
                return $this->redirectToRoute('app_index_index');
                // ou on envoit une exception
                //throw $this->createAccessDeniedException();
            }

            // si l'annonce contient une image
            // en imbriqué (si première image n'est pas nulle puis si la seconde ne l'est pas...)
            if (!is_null($post->getImage())) {
                $originalImage = $post->getImage();
                // on sette l'image avec un objet File sur l'emplacement de l'image
                //  pour le traitement par le formulaire
                $post->setImage(
                    new File($this->getParameter('upload_dir') . $originalImage)
                );
                if (!is_null($post->getImage2())) {
                    $originalImage2 = $post->getImage2();
                    $post->setImage2(
                        new File($this->getParameter('upload_dir') . $originalImage2)
                    );
                    if (!is_null($post->getImage3())) {
                        $originalImage3 = $post->getImage3();
                        $post->setImage3(
                            new File($this->getParameter('upload_dir') . $originalImage3)
                        );
                    }
                }

            }
        }

        // création du formulaire relié à la catégorie Post
        $form = $this->createForm(PostType::class, $post);

        // le formulaire analyse la requête
        // et fait le mapping avec l'entité s'il a été soumis
        $form->handleRequest($request);

        // si le formulaire a été soumis
        if ($form->isSubmitted()) {
            // si les validations à partir des annotations
            // dans l'entité Post sont ok
            if ($form->isValid()) {
                /** @var UploadedFile $image */
                /** @var UploadedFile $image2 */
                /** @var UploadedFile $image3 */
                //                astuce stackoverflow
                $image = $form->get('image')->getData();
                $image2 = $form->get('image2')->getData();
                $image3 = $form->get('image3')->getData();

                // s'il y a eu une image uploadée
                if (!is_null($image)) {
                    // nom sous lequel on va enregistrer l'image
                    $filename = uniqid() . '.' . $image->guessExtension();

                    $image->move(
                    // répertoire dans lequel on déplace l'img (ici public/images)
                    // cf config/services.yaml
                        $this->getParameter('upload_dir'),
                        //sous quel nom
                        $filename
                    );
                    // on sette l'atrribut image de l'article avec le nom du fichier
                    // pour l'enregistrement en bdd
                    $post->setImage($filename);

                    // en modification on supprime l'ancienne image
                    // s'il y en a une
                    if (!is_null($originalImage)) {
                        unlink($this->getParameter('upload_dir') . $originalImage);
                    }
                } else {
                    // en modification, sans upload, on sette l'image
                    //avec le nom de l'ancienne image
                    $post->setImage($originalImage);
                }
                if (!is_null($image2)) {
                    $filename2 = uniqid() . '.' . $image2->guessExtension();
                    $image2->move(
                    // répertoire dans lequel on déplace l'img (ici public/images)
                    // cf config/services.yaml
                        $this->getParameter('upload_dir'),
                        //sous quel nom
                        $filename2
                    );
                    $post->setImage2($filename2);
                    if (!is_null($originalImage2)) {
                        unlink($this->getParameter('upload_dir') . $originalImage2);
                    }
                } else {
                    $post->setImage2($originalImage2);
                }

                if (!is_null($image3)) {
                    $filename3 = uniqid() . '.' . $image3->guessExtension();
                    $image3->move(
                    // répertoire dans lequel on déplace l'img (ici public/images)
                    // cf config/services.yaml
                        $this->getParameter('upload_dir'),
                        //sous quel nom
                        $filename3
                    );
                    $post->setImage3($filename3);
                    if (!is_null($originalImage3)) {
                        unlink($this->getParameter('upload_dir') . $originalImage3);
                    }
                } else {
                    $post->setImage3($originalImage3);
                }

                // pour setter l'auteur avec l'utilisateur connecté
                $post->setAuthor($this->getUser());

                // enregistrement de l'annonce en bdd
                $manager->persist($post);
                $manager->flush();
                // message de confirmation
                $this->addFlash('success', 'L\'annonce est enregistrée');
                return $this->redirectToRoute('app_user_profil',
                    [
                        'id' => $post->getAuthor()->getId()
                    ]
                );
            } else {
                // message de confirmation
                $this->addFlash('danger', 'Le formulaire contient des erreurs');
            }
        }

        return $this->render(
            'post/edit.html.twig',
            [
                // on passe au template une "vue" du formulaire
                // renvoyée par la méthode createView
                'form' => $form->createView(),
                'original_image' => $originalImage,
                'original_image2' => $originalImage2,
                'original_image3' => $originalImage3

            ]
        );
    }
    // *******************************************************************************
    //                  FIN FORMULAIRE AJOUT et MODIFICATION ANNONCE
    // *******************************************************************************


    // *******************************************************************************
    //                          AFFICHAGE D'UNE ANNONCE
    //                  (avec formulaire pour dépôt d'un commentaire)
    // *******************************************************************************
    /**
     *
     * @Route("/{id}", requirements={"id": "\d+"})
     */
    public
    function showPost($id,
                      CommentRepository $commentRepository,
                      Request $request,
                      EntityManagerInterface $manager, UserFavoritesRepository $favoritesRepository, Post $post)
    {
        $manager = $this->getDoctrine()->getManager();
        $annoncechoisie = $manager->find(Post::class, $id);
        $comments = $commentRepository->findBy(['post' => $annoncechoisie]);

        $comment = new Comment();

        // création du formulaire relié à l'article pour déposer un commentaire
        $form = $this->createForm(CommentType::class, $comment);

        // le formulaire analyse la requête
        // et fait le mapping avec l'entité s'il a été soumis
        $form->handleRequest($request);

        // si le formulaire a été soumis
        if ($form->isSubmitted()) {
            // si les validations à partir des annotations
            // dans l'entité Catégory sont ok
            if ($form->isValid()) {
                // enregistrement de la catégorie de bdd
                $comment
                    ->setAuthor($this->getUser())
                    ->setPost($annoncechoisie);

                $manager->persist($comment);
                $manager->flush();
                // message de confirmation
                $this->addFlash('success', 'Votre commentaire est enregistré');
                return $this->redirectToRoute(
                // la page sur laquelle on est
                    $request->get('_route'),
                    [
                        'id' => $annoncechoisie->getId()
                    ]);

            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }

        // Vérification si le favoris existe en BDD
        // Permet d'afficher un coeur "plein" ou "vide" en fonction de la réponse
        // Retourne NULL si le post n'est pas en favoris
        $favorite = $favoritesRepository->findOneBy(array('user' => $this->getUser(), 'post' => $post));


        return $this->render(
            'post/index.html.twig',
            [
                'post' => $annoncechoisie,
                'comments' => $comments,
                'form' => $form->createView(),
                'user' => $this->getUser(),
                'favoris' => $favorite

            ]);
    }

    // *******************************************************************************
    //                  FIN PAGE AFFICHAGE D'UNE ANNONCE
    //              (incluant formulaire pour dépôt d'un commentaire)
    // *******************************************************************************


    // *******************************************************************************
    //                          SUPPRESSION D'UNE ANNONCE
    // *******************************************************************************

    /**
     * @param EntityManagerInterface $entityManager
     * @param Post $post
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/suppression/{id}")
     */
    public
    function delete(EntityManagerInterface $entityManager, Post $post)
    {
        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'L\'annonce a été supprimée');
        return $this->redirectToRoute('app_user_profil',
            [
                'id' => $post->getAuthor()->getId()
            ]
        );

    }

    // *******************************************************************************
    //                          FIN SUPPRESSION D'UNE ANNONCE
    // *******************************************************************************


}