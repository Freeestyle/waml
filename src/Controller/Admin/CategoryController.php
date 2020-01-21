<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller\Admin
 * @Route("/categorie")
 */
class CategoryController extends AbstractController
{
    /**
     * Url de la page est admin/categorie (cf config/routes/annotation.yaml
     * nom de la route est app_admin_category_index
     *
     * @Route("/")
     */
    public function index(CategoryRepository $repository, RegionRepository $regionRepository)
    {
        // $categories = $repository->findAll();
        // avel le findAll() les éléments vont pour ordre de "nom" par défaut
        // éq d'un findAll() avec un tri sur l' id
        $categories = $repository->findBy([], ['id' => 'ASC']);
        $region = $regionRepository->findBy([], ['name' => 'ASC']);


        return $this->render(
            'Admin/category/index.html.twig',
            [
                'categories' => $categories,
                'regions' => $region
            ]
        );
    }
    /**
     * {id} est optionnel,
     * vaut null par défaut et
     * doit être un nombre si renseigné
     *
     * @Route("/edition/{id}", defaults={"id":null}, requirements={"id": "\d+"})
     */
    public function edit(
        Request $request,
        EntityManagerInterface $manager,
        $id
    )
    {
        $originalImage = null; //je definis l'image null par défaurl

        if (is_null($id)) {
            //si le parametre variable {id} est vide >> création
            //instantiation d'une category
            $category = new Category();
        } else { //modification
            //je demande au manager d'aller chercher la catègory
            // à laquelle corresponde le {id de la partie variable}
            $category = $manager->find(Category::class, $id);

            //404 si le id n'est pas en bdd
            if (is_null($category)) {
                throw new NotFoundHttpException();
            }
            //on verifie si l'article à modifier a un img
            if (!is_null($category->getlogo())) {
                // on met le nom du ficher venant de la bdd
                // ds la  variable
                $originalImage = $category->getlogo();
                // on sette l'image avec un nouveau obj File sur l'emplacement de l'img
                // pour le traitement par le formulaire
                $category->setlogo(
                    new File($this->getParameter('upload_dir') . $originalImage));

            }

        }



        // création d'un form, rélié à la catégorie
        // auquel on va passer le $category qu'on viet de créer avec la methode createForm()
        $form = $this->createForm(CategoryType::class, $category);

        /*
         * Le form analise la requete
         * et fait le mapping avec l'entité s'il a été soumis
         */
        $form->handleRequest($request);
        //dump($category);

        // si le form a été soumis
        if ($form->isSubmitted()) {
            // si les validations à partir des annotations
            // ds l'entité Category sont ok
            if ($form->isValid()) {

                /**
                 * IMPORTANT >>
                 * @var UploadedFile $image
                 */
                $image = $form->get('logo')->getData();
                //s'il y a un image uploadée >> je lance son traitement
                if (!is_null($image)) {
                    // nom sous lequel on va enregistrer l'image
                    // guessExtension() > trouve extension à partir d'un file
                    $filename = uniqid() . '.' . $image->guessExtension();

                    // deplacement image uploadé du emplacement temp à son emplacement definitif
                    $image->move(
                    // repertoire vers lequel on va deplacer l'image (public/images)
                    //cf config/services.yaml
                        $this->getParameter('upload_dir'),
                        // nom unique du ficher
                        $filename
                    );
                    //set de l'attribut image de l'article avec le nom du ficher unique
                    $category->setLogo($filename);

                    // en modification, on supprime l'ancienne photo
                    // s'il y en a une
                    if (!is_null($originalImage)) {
                        unlink($this->getParameter('upload_dir') . $originalImage);
                    }

                } else {
                    // en modification, sans upload, on sette l'image
                    // avec le nom de l'ancienne image
                    $category->setImage($originalImage);
                }

                //EntityManager enregistre la catégorie ds la bdd
                $manager->persist($category);
                $manager->flush();

                //message de confirmation
                $this->addFlash('success', 'La catégorie est enregistrée');

                //redirection vers la page de liste
                return $this->redirectToRoute('app_admin_category_index');
            } else {
                // dump($form->getErrors(false, true)); // il faut faire en suite une iteration pour recuperer tous les messages un par un
                //message d'erreur
                $this->addFlash('error', 'Le formulare contient des erreurs');
            }
        }

        return $this->render(
            'Admin/category/edit.html.twig',
            [
                // passage du form
                // on passe un obj de la class FormView() (= la view du form)
                'form' => $form->createView()
            ]
        );
    }

    /**
     * pas de défault null car le id est obligatoire pour la suppression
     * @Route("/supression/{id}", requirements={"id": "\d+"})
     */
    public function delete(
        EntityManagerInterface $manager,
        Category $category
    )
    {
        /*
         * Si j'ai un article ds la categorie, je ne peux pas la supprimer
         */
        // 1ère methode
        // $nbArticles = $category->getArticles()->count();
        // if (0 != $nbArticles) {
        // 2éme methode
        if (!$category->getPosts()->isEmpty()) {
            $this->addFlash('error', 'La categorie ne peut pas être supprimé car elle contient des articles');

        } else {

            //suppression de la catégorie en bdd
            $manager->remove($category);
            //remove comme persist a besoin d'un flush
            $manager->flush();

            $this->addFlash('success', 'La catégorie est supprimée');

        }
        return $this->redirectToRoute('app_admin_category_index');
    }
}
