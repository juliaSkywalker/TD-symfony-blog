<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
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
     * @Route("/{id}")
     */
    public function index(Category $category)
    {
        /**
         *
         * afficher les 5 derniers articles de la catégorie
         * par ordre de date de publication croossante avec un lien vers la page article
         */

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Article::class);


        $articles = $repository->findBy(['category' => $category], ['publicationDate' => 'DESC'], $limit = 5);


        return $this->render(
            'category/index.html.twig', [
            'category' => $category,
            'articles' => $articles
        ]);
    }


    public function menu()
    {
        $repository = $this->getDoctrine()->getRepository(Category::class);

        $categories = $repository->findBy([], ['name' => 'asc']);

        return $this->render('category/menu.html.twig',
            [
                'categories' => $categories
            ]
        );

    }


}
