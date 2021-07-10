<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Uploader;
use App\Search\SearchPost;
use App\Service\Paginator;
use App\Form\SearchFormType;
use App\Form\AccountEditType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAccountController extends AbstractController
{
    /**
     * Permet de renvoyer les utilisateurs paginés
     * 
     * @Route("/admin/users/{page<\d+>?1}", name="admin_users_index")
     *
     * @param Paginator $paginator
     * @param int $page
     * @return Response
     */
    public function index(Request $request, Paginator $paginator, int $page, UserRepository $repo): Response
    {
        $search = new SearchPost();

        $form = $this->createForm(SearchFormType::class, $search);
        $form->handleRequest($request);

        /** @var User[] contenant des utilisateurs paginés */
        $users = $paginator
                    ->setParams([$repo, $page, $search->query, 'u', 'lastName'])
                    ->getData();
        
        return $this->render('admin/user/index.html.twig', [
            'form'          => $form->createView(),
            'users'         => $users,
            'countPages'    => $paginator->getCountPages(),
            'page'          => $page
        ]);
    }

    /**
     * Permet de modifier un utilisateur
     * 
     * @Route("/admin/user/{id}/edit", name="admin_user_edit", requirements={"id"="\d+"})
     *
     * @param User $user
     * @param Request $request
     * @param Uploader $uploader
     * @param ObjectManager $manager
     * @return Response
     */
    public function edit(User $user, Request $request, Uploader $uploader, ObjectManager $manager): Response
    {          
        // Création du formulaire
        $form = $this->createForm(AccountEditType::class, $user);

        // Manipulation de formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):
            /** @var UploadedFile|null $picture contenant la picture de l'utilisateur */
            $picture = $form['picture']->getData();

            // S'il y a la picture
            if ($picture && $picture instanceof UploadedFile):
                // Suppression d'ancien picture
                $deleted = unlink($this->getParameter('picture_directory').DIRECTORY_SEPARATOR.$user->getPicture());

                if (!$deleted):
                    return $this->redirectToRoute("admin_user_edit", [
                        'id' => $user->getId()
                    ], 301);
                endif;

                // Le nom de la picture
                $filename = $uploader->upload(
                    $picture, 
                    $this->getParameter('picture_directory'),
                    "Nous n'avons pas pû nous uploader la picture! Réessayer ultérieurement"
                );
                // Rajout du nom de la picture
                $user->setPicture($filename);
            endif;

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                "success",
                "Merci '{$this->getUser()->getFullName()}' d'avoir modifié l'utilisateur {$user->getFullName()}"
            );

            return $this->redirectToRoute("admin_users_index");

        endif;

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);

    }

    /**
     * Permet de supprimer un utilisateur
     * 
     * @Route("/admin/user/{id}/remove", name="admin_user_remove", requirements={"id"="\d+"})
     *
     * @param User $user
     * @param ObjectManager $manager
     * @return Response
     */
    public function remove(User $user, ObjectManager $manager): Response
    {   
        // Tous les postes de l'utilisateur
        $posts = $user->getPosts();

        // Si le poste acceptant le foreach
        if ($posts instanceof \Traversable):
            foreach ($posts as $post):

                // Suppression de l'image
                $deletedImage = unlink($this->getParameter('images_directory') . DIRECTORY_SEPARATOR . $post->getImage());
                if (!$deletedImage):
                    $this->createNotFoundException("Nous n'avons pas pû supprimer cette image! Réessayer");
                endif;

                // Suppression de la video
                $deletedVideo = unlink($this->getParameter('videos_directory') . DIRECTORY_SEPARATOR . $post->getVideo());
                if (!$deletedVideo):
                    $this->createNotFoundException("Nous n'avons pas pû supprimer cette video! Réessayer");
                endif;
                
                if ($post->getImages() instanceof \Traversable):

                    foreach ($post->getImages() as $image):
                        // Suppression de carousel
                        $deleted = unlink($this->getParameter('carousel_directory') . DIRECTORY_SEPARATOR . $image->getImg());

                        if (!$deleted):
                            $this->createNotFoundException("Nous n'avons pas pû supprimer cette image de carousel, Réessayer");
                        endif;
                    endforeach;

                endif;
                
            endforeach;

            $manager->remove($user);
            $manager->flush();

            $this->addFlash(
                "success",
                "Merci monsieur '{$this->getUser()->getFullName()}' d'avoir supprimé l'utilisateur {$user->getFullName()}"
            );

            return $this->redirectToRoute('admin_users_index', [], 301);

        endif;
    }

}
