<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Uploader;
use App\Form\RegisterType;
use App\Form\AccountEditType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{   
    /**
     * Permet de créer un compte pour l'utilisateur
     *
     * @Route("/account/register", name="account_register")
     * 
     * @param Request $request
     * @param Uploader $uploader
     * @param UserPasswordEncoderInterface $encoder
     * @param ObjectManager $manager
     * @return Response
     */
    public function register(
        Request $request, 
        Uploader $uploader, 
        UserPasswordEncoderInterface $encoder, 
        ObjectManager $manager
    ): Response {

        // Nouvel utilisateur
        $user = new User();
        // Création d'objet $form
        $form = $this->createForm(RegisterType::class, $user);
        // Manipulation de données
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            // Le mot de passe
            $passwordEncoded = $encoder->encodePassword($user, $user->getHash()); 

            // Ajouter le mot de passe hashé
            $user->setHash($passwordEncoded);

            /** @var UploadedFile $picture contenant la picture */
            $picture = $form['picture']->getData();

            if ($picture && $picture instanceof UploadedFile):
                // Le nom de la picture
                $filename = $uploader->upload(
                    $picture, 
                    $this->getParameter('picture_directory'),
                    "La picture n'a pas été uploadé, Réssayer ultérieurement"
                );
                $user->setPicture($filename);
            endif;

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Vous vous êtes connecté, veuillez vous connecter');

            return $this->redirectToRoute('account_login', [], 301);
        endif;

        return $this->render('account/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de se connecter l'utilisateur
     * 
     * @Route("/account/login", name="account_login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response 
    {
        // Au niveau de security.yaml

        // Le dernier utilisateur
        $lastUser = $authenticationUtils->getLastUsername();
        // S'il ya des erreurs lors de l'authentication
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('account/login.html.twig', [
            'lastUser' => $lastUser,
            'error'    => $error
        ]);
    } 

    /**
     * Permet de se déconnecter l'utilisateur
     * 
     * @Route("/account/logout", name="account_logout")
     *
     * @return void
     */
    public function logout(): void 
    {
        // Au niveau de security.yaml
    }

    /**
     * Permet d'éditer un utilisateur
     * 
     * @Route("/account/{id}-{slug}/edit", name="account_edit")
     * 
     * @Security("is_granted('ROLE_USER') and user == user")
     *
     * @param User $user
     * @param Request $request
     * @param Uploader $uploader
     * @param ObjectManager $manager
     * @return Response
     */
    public function edit(
        User $user, 
        Request $request, 
        Uploader $uploader, 
        ObjectManager $manager
    ): Response {
        // Création du formulaire
        $form = $this->createForm(AccountEditType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            /** @var UploadedFile $picture contenant la photo de profile */
            $picture = $form['picture']->getData();

            if ($picture && $picture instanceof UploadedFile):

                // L'ancien nom de picture de l'utilisateur
                $oldName = $user->getPicture();

                $deleted = unlink($this->getParameter('picture_directory').'/'.$oldName);

                // La suppression s'est passé correctement
                if (!$deleted):
                    throw $this->createNotFoundException("Nous n'avons pas pû supprimer l'ancien picture! Réessayer en revenant à la page précédente");
                endif;

                $filename = $uploader->upload(
                    $picture, 
                    $this->getParameter('picture_directory'),
                    "Nous n'avons pas pû modifier la photo de profile"
                );
                $user->setPicture($filename);

            endif;
            
            $manager->persist($user);
            $manager->flush();

            $this->addFlash("success", "Monsieur {$user->getFullName()}, votre compte a été modifié avec succès");

            return $this->redirectToRoute('user_show', [
                'id'    => $user->getId(),
                'slug'  => $user->getSlug()
            ], 301);

        endif;

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
            'fullName' => $user->getFullName(),
            'picture' => $user->getPicture()
        ]);

    }

}
