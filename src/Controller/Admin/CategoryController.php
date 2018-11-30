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
     * @Route("/edition")
     */
    public function edit(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categorie = new Category();
        $form = $this->createForm(CategoryType::class, $categorie);
        //formulaire analyse la requête http
        $form->handleRequest($request);
        //si le formulaire a été envoyé
        if ($form->isSubmitted()) {
            //si mon form est valide à partir des annotation dans l'entité Catégory son ok
            if($form->isValid()){
                //enregistrement de la catégorie dans la bdd
                $em->persist($categorie);
                $em->flush();
                //message de confirmation
                $this->addFlash('success','La catégorie est crée');
                //redirection vers la liste
                return $this->redirectToRoute('app_admin_category_index');
            }else{
                $this->addFlash('error', 'Le formulaire contient des erreurs.');
            }

        }


        return $this->render('admin/category/edit.html.twig', [
            //passage du formulaire au template
            'form' => $form->createView()]);

    }


}