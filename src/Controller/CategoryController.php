<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\RegionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller
 * @Route("/categorie")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(
        CategoryRepository $categoryRepository
    )
    {
        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);

        return $this->render(
            'category/index.html.twig',
            [
                'categories' => $categories

            ]
        );
    }

    /**
     * @param CategoryRepository $repository
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/annonces/{id}")
     */
    public function menu(Category $category,
                         PostRepository $postRepository,
                         UserRepository $userRepository)
    {

        $posts = $postRepository->findBy(
            ['category' => $category],
            ['publicationDate' => 'DESC']
        );

        $users = $userRepository->findBy(
            ['category' => $category],
            ['publicationDate' => 'DESC']
        );

        return $this->render(
            'category/index.html.twig',
            [
                'category' => $category,
                'posts' => $posts,
                '$users' => $users
            ]
        );
    }

    public function footer(CategoryRepository $categoryRepository, RegionRepository $regionRepository)
    {
        $regions = $regionRepository->findBy([], ['name' => 'ASC']);
        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);
        return $this->render(
            'footer.html.twig',
            [
                'categories' => $categories,
                'regions' => $regions
            ]
        );
    }
}