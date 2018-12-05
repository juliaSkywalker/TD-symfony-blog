<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/{id}")
     */
    public function index(Request $request, Article $article)
    {
        /**
         * sous l'article, si l'utilisateur n'est pas connecté, l'inviter à la fe faire pour pouvoir écrir un commantaire
         * sinon, lui afficher un formulaire avec un textarea  pour pourvoir écrire un commentaire
         *
         * nécessite de se créer une entity pour les commentaire, qui va avoir un id
         * - content(text en bdd)
         * - date de publication  (datetime)
         * - user(l'utilisateur qui écrit le commentaire-
         * article (larticle sur lequel on écrit le commentaire
         * nécessite  le form type qui va avec contenant me textarea,
         * le contenu du commentaire
         * ne doit pas etre vide
         *
         *
         * liste les commentaire en dessous, avec le nom de l'utilisateur la date de publication et le contenu du message
         */


        //entity manager
        $em = $this->getDoctrine()->getManager();


        $repository = $em->getRepository(Comment::class);
        $comment = new Comment();


        $date = new \DateTime();
        $comment->setPublicationDate($date);

        $comment->setArticle($article);


        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);


        if ($form->isSubmitted()) {
            //si mon form est valide à partir des annotation dans l'entité Catégory son ok
            if ($form->isValid()) {

                $user = $this->getUser();
                $comment->setUser($user);
                $em->persist($comment);
                $em->flush();
                $this->addFlash('success', 'Votre commentaire a bien été enregistré');

            }
        }


        return $this->render('article/index.html.twig', [
            'article' => $article,
            'comment' => $comment,
            'form' => $form->createView()
        ]);


    }


}
