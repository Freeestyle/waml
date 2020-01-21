<?php

namespace App\Controller\Admin;


use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PostController
 * @package App\Controller\Admin
 * @Route("/post")
 */
class PostController extends AbstractController
{

    /**
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{id}", defaults={"id":null}, requirements={"id": "\d+"})
     */
    public function index(PostRepository $postRepository, UserRepository $userRepository, $id)
    {
        if (empty($id)) {
            $posts = $postRepository->findBy([], ['publicationDate' => 'DESC']);
        } else {
            $user = $userRepository->find($id);
            $posts = $postRepository->findBy(
                [
                    'author' => $user
                ],
                ['publicationDate' => 'DESC']);
        }

        return $this->render('Admin/post/index.html.twig',
            [
                'posts' => $posts
            ]);
    }

// ******************************************************************************************************
//                  FORMULAIRE MODIFICATION ANNONCE (à retrouver aussi chez le USER)
//              permet à l'admin d'accéder au formulaire de modification de l'annonce
// ******************************************************************************************************
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

        $post = $manager->find(Post::class, $id);

        // si l'annonce contient une image
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

        // création du formulaire relié à la catégorie Post
        $form = $this->createForm(PostType::class, $post);

        // analyse de requête par le form // mapping avec l'entité
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // si les validations à partir des annotations dans l'entité Post sont ok
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
                        //sous le nom
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
                    // avec le nom de l'ancienne image
                    $post->setImage($originalImage);
                }
                if (!is_null($image2)) {
                    $filename2 = uniqid() . '.' . $image2->guessExtension();
                    $image2->move(
                        $this->getParameter('upload_dir'),
                        //sous le nom
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

                // enregistrement de l'annonce en bdd
                $manager->persist($post);
                $manager->flush();
                // message de confirmation
                $this->addFlash('success', 'L\'annonce est enregistrée');
                return $this->redirectToRoute('app_admin_post_index');
            } else {
                // message de confirmation
                $this->addFlash('danger', 'Le formulaire contient des erreurs');
            }
        }
        return $this->render(
            'post/edit.html.twig',
            [
                'form' => $form->createView(),
                'original_image' => $originalImage,
                'original_image2' => $originalImage2,
                'original_image3' => $originalImage3
            ]
        );
    }
// *******************************************************************************
//                  FIN FORMULAIRE MODIFICATION ANNONCE
// *******************************************************************************


    /**
     * @param EntityManagerInterface $entityManager
     * @param Post $post
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/suppression/{id}")
     */
    public function delete(EntityManagerInterface $entityManager, Post $post)
    {
        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'L\'annonce a été supprimée');
        return $this->redirectToRoute('app_admin_post_index',
            [
                'id' => $post->getAuthor()->getId()
            ]
        );

    }




// *******************************************************************************
//                          ANIMATION AJAX APERCU CONTENU ANNONCE
// *******************************************************************************

    /**
     * @Route("/ajax/contenu/{id}")
     */
    // paramConverter ()
    public function ajaxContent(Post $post)
    {
        return new Response(nl2br($post->getContent()));
    }

}