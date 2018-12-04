<?php
/**
 * Created by PhpStorm.
 * User: Etudiant
 * Date: 03/12/2018
 * Time: 17:01
 */

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ArticleController
 * @package App\Controller\Admin
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Article::class);
        $articles = $repository->findBy([], ['publicationDate' => 'DESC']);
        return $this->render('admin/article/index.html.twig', [
                'articles' => $articles

            ]
        );


    }

    /**
     * {id} est optionnel et doit être un nombre
     * @Route("/edition/{id}", defaults={"id":null}, requirements={"id":"\d+"})
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $originalImage = null;
        if (is_null($id)) {//creation

            $article = new Article();
            $user = $this->getUser();
            $article->setAuthor($user);

            //$article->setAuthor($this->getUser());
            //$article->setPublicationDate(new\DateTime()

            $date = new \DateTime();
            $article->setPublicationDate($date);
        } else {//modification

            $article = $em->find(Article::class, $id);


            if (!is_null($article->getImage())) {
                //nom du fichier venant de la bdd
                $originalImage = $article->getImage();
                //on sette l'image
                //pour le traitement par le formulaire
                $article->setImage(
                    new File($this->getParameter('upload_dir') . $originalImage)
                );
            }


            //404 si l'id reçu dans l'url n'est pas en bdd
            if (is_null($article)) {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(ArticleType::class, $article);
        //formulaire analyse la requête http
        $form->handleRequest($request);
        //si le formulaire a été envoyé
        if ($form->isSubmitted()) {
            //si mon form est valide à partir des annotation dans l'entité Catégory son ok
            if ($form->isValid()) {

                /**@var UploadedFile $image */
                $image = $article->getImage();

                //s'il y a une image uploadée
                if (!is_null($image)) {
                    //nom de l'image dans notre application
                    $filename = uniqid() . '.' . $image->guessExtension();
                    //équivalent  de move_uploaded_file()
                    $image->move(
                    //repertoire de destination
                    //cf le parametre config/service.yaml
                        $this->getParameter('upload_dir'),
                        //le nom du fichier
                        $filename

                    );
                    //on sette l'attribut image de l'article avec le nom
                    //de l'iamge pour enregistrement en bdd
                    $article->setImage($filename);
                    //si on avait déjà une image pour notre article:
                    //en modification, on supprime l'ancienne image s'il y en a une
                    if (!is_null($originalImage)) {
                        //unlink(chemin du fichier)
                        unlink($this->getParameter('upload_dir') . $originalImage);


                    }
                } else {
                    //sans upload, pour la modification, on sette l'attribut

                    $article->setImage($originalImage);
                }


                //enregistrement de la catégorie dans la bdd
                $em->persist($article);
                $em->flush();

                //message de confirmation
                $this->addFlash('success', 'article a été créé');
                //redirection vers la liste
                return $this->redirectToRoute('app_admin_article_index');
            } else {
                //message d'erreur
                $this->addFlash('error', 'Le formulaire contient des erreurs.');
            }

        }


        return $this->render('admin/article/edit.html.twig', [
            //passage du formulaire au template
            'form' => $form->createView(),
            'original_image' => $originalImage

        ]);


    }

    /**
     * @Route("/suppression/{id}")
     */
    public function delete(Article $article)
    {

        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        $this->addFlash('success', 'Votre article a bien été supprimé');


        return $this->redirectToRoute('app_admin_article_index');
    }
}