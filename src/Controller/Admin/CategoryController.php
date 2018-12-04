<?php
/**
 * Created by PhpStorm.
 * User: Etudiant
 * Date: 30/11/2018
 * Time: 14:54
 */

namespace App\Controller\Admin;


use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller\Admin
 * @Route("/Categorie")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Category::class);
        //$categories=$repository->findAll();
        //ou
        $categories = $repository->findBy([], ['name' => 'asc']);
        //l'intéret du findby on va pouvoir lui rajouter d'autres parametres
        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * {id} est optionnel et doit être un nombre
     * @Route("/edition/{id}", defaults={"id":null}, requirements={"id":"\d+"})
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (is_null($id)) {//creation

            $category = new Category();

        } else {//modification

            $category = $em->find(Category::class, $id);
            //404 si l'id reçu dans l'url n'est pas en bdd
            if (is_null($category)) {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(CategoryType::class, $category);
        //formulaire analyse la requête http
        $form->handleRequest($request);
        //si le formulaire a été envoyé
        if ($form->isSubmitted()) {
            //si mon form est valide à partir des annotation dans l'entité Catégory son ok
            if ($form->isValid()) {
                //enregistrement de la catégorie dans la bdd
                $em->persist($category);
                $em->flush();
                //message de confirmation
                $this->addFlash('success', 'La catégorie est crée');
                //redirection vers la liste
                return $this->redirectToRoute('app_admin_category_index');
            } else {
                //message d'erreur
                $this->addFlash('error', 'Le formulaire contient des erreurs.');
            }

        }


        return $this->render('admin/category/edit.html.twig', [
            //passage du formulaire au template
            'form' => $form->createView()]);

    }

    /**
     * @Route("/suppression/{id}")
     */
    public function delete(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
//        $repository = $em->getRepository(Category::class);
//        $repository->findOneBy(
//            [
//                "name" => $article->getCategory()
//            ]
//
//        );
        if ($category->getArticle()->count() == 0) {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'La catégorie est supprimée');
        } else {
            $this->addFlash('error', 'Impossible de supprimer une catégorie utilisé par un ou plusieurs articles');
        }

        return $this->redirectToRoute('app_admin_category_index');
    }
}