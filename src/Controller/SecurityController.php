<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {


            if ($form->isValid()) {
                //encode le mot de passe, à partir de la config encoders de security.yaml
                $password = $passwordEncoder->encodePassword(
                    $user,
                    $user->getPlainPassword()
                );
                $user->setPassword($password);

                $em = $this->getDoctrine()->getManager();
                //enregistrement de la catégorie dans la bdd
                $em->persist($user);
                $em->flush();
                //message de confirmation
                $this->addFlash('success', 'votre compte est créé');
                return $this->redirectToRoute('app_index_index');
            } else {
                //message d'erreur
                $this->addFlash('error', 'Le formulaire contient des erreurs.');
            }
        }


        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {

        //traitement du formulaire par security
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();


        if (!empty($error)) {
            dump($error);
            $this->addFlash('error', 'Identifiants incorrect');
        }
        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername
            ]
        );

    }
}
