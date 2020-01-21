<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\SearchPostType;
use App\Entity\Region;
use App\Repository\CategoryRepository;
use App\Repository\UserFavoritesRepository;
use App\Repository\PostRepository;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController
{

    /**
     * @Route("/")
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @param RegionRepository $regionRepository
     * @param PostRepository $postRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(
        CategoryRepository $categoryRepository, RegionRepository $regionRepository, PostRepository $postRepository
    )
    {
        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);
        $region = $regionRepository->findBy([], ['name' => 'ASC']);
        $posts = $postRepository->findRandom();

        return $this->render(
            'index/index.html.twig',
            [
                'categories' => $categories,
                'regions' => $region,
                'posts' => $posts
            ]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{name}/annonces")
     */
    public function region(Region $region, Request $request, PostRepository $postRepository, $name, RegionRepository $regionRepository, UserFavoritesRepository $favoriteRepository)
    {
        // Recuperation des annonces en fonction de la region choisie (carte ou liste)
        if (empty($name)) {
            $posts = $postRepository->findBy([], ['publicationDate' => 'DESC'], 5);
            $this->redirectToRoute('app_index_index');
        } else {
            if (!empty($name)) {
                $regionId = $region->getId();
                $posts = $postRepository->findBy(
                    [
                        'region' => $regionId
                    ],
                    [
                        'publicationDate' => 'DESC'
                    ]
                );
            }
        }
        $searchForm = $this->createForm(SearchPostType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted()) {
            $posts = $postRepository->search((array)$searchForm->getData());
        }

        return $this->render('index/post.html.twig',
            [
                'posts' => $posts,
                'search_form' => $searchForm->createView(),
            ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/annonces/{name}")
     */
    public function post(Category $category, Request $request, PostRepository $postRepository, $name, CategoryRepository $categoryRepository, UserFavoritesRepository $favoriteRepository)
    {
        $posts = '';
        // Recuperation des annonces en fonction de la categorie choisie (carte ou liste)
        if (empty($name)) {
            $posts = $postRepository->findBy([], ['publicationDate' => 'DESC'], 5);
            $this->redirectToRoute('app_index_index');
        } else {
            if (!empty($name)) {
                $categoryId = $category->getId();
                $posts = $postRepository->findBy(
                    [
                        'category' => $categoryId
                    ],
                    [
                        'publicationDate' => 'DESC'
                    ]
                );
            }
        }
        $searchForm = $this->createForm(SearchPostType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted()) {
            $posts = $postRepository->search((array)$searchForm->getData());
        }

        return $this->render('index/post.html.twig',
            [
                'posts' => $posts,
                'search_form' => $searchForm->createView(),
            ]);
    }
}
