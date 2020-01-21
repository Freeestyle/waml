<?php

namespace App\Controller;


use App\Entity\Post;
use App\Entity\UserFavorites;
use App\Repository\UserFavoritesRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavoriteController
 * @package App\Controller
 * @Route("/favoris")
 */
class FavoriteController extends AbstractController
{
    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/ajouter/{id}")
     */
    public function addFav(
        EntityManagerInterface $manager,
        PostRepository $postRepository,
        Post $post, $id,
        UserFavoritesRepository $userFavoritesRepository)
    {
        // Verification si le favorite existe déjà
        $favorite = $userFavoritesRepository->findOneBy(
            [
                'post' => $id,
                'user' => $this->getUser()
            ]);
        // S'il n'existe pas, création d'un nouveau favoris dans la table user_favorites
        if (is_null($favorite)) {
            $favorite = new UserFavorites();
            $favorite
                ->setPost($post)
                ->setUser($this->getUser());
            $manager->persist($favorite);
            $manager->flush();
            // Ici la response sert pour le code AJAX, pour pouvoir afficher ou non le coeur "plein",
            // si on ajoute une annonce en favoris
            return new Response('add');
        } else {
            // S'il existe, on supprime le favoris dans la table user_favorites
            $manager->remove($favorite);
            $manager->flush();
            // si on supprime un favoris, le coeur repasse "vide"
            return new Response('remove');
        }

    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/profil/")
     */
    public function profilFavs(PostRepository $postRepository)
    {
        // Pour afficher la liste des annonces en favoris dans le profil utilisateur
        $user = $this->getUser();
        $userId = $user->getId();
        $posts = $postRepository->findBy(['author' => $userId], ['publicationDate' => 'DESC']);
        return $this->render('favoris/favoris.html.twig',
            [
                'user' => $user,
                'original_photo' => $user->getPhoto(),
                'posts' => $posts,
                'favorites' => $user->getFavorites()
            ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserFavorites $favorite
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/suppression/{id}")
     */
    public function delete(EntityManagerInterface $entityManager, UserFavoritesRepository $userFavoritesRepository, UserFavorites $id)
    {
        $favorite = $userFavoritesRepository->find($id);

        $entityManager->remove($favorite);
        $entityManager->flush();

        $this->addFlash('success', 'le favoris a été supprimé');
        return $this->redirectToRoute('app_favorite_profilfavs');

    }
}
