<?php


namespace App\Controller;


use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class CommentController
 * @package App\Controller\Admin
 * @Route("/commentaire")
 *
 */

class CommentController extends AbstractController
{

    /**
     * l'id de l'annonce dont on veut voir les commentaires
     * @Route("/{id}")
     */
    public function index(Post $post)
    {
        return $this->render(
            'Admin/comment/index.html.twig',
            [
                'post'=>$post
            ]
        );
    }


    /**
     * @Route("/suppression/{id}")
     *
     */
    public function delete(EntityManagerInterface $manager, Comment $comment)
    {
        $manager->remove($comment);
        $manager->flush();

        $this->addFlash('success', 'le commentaire est supprimé');

        return $this->redirectToRoute('app_comment_index',
            [
                'id' =>$comment->getPost()->getId()
            ]);
    }
}