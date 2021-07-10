<?php

namespace App\Controller;

use App\Entity\Post;
use App\Service\Uploader;
use App\Form\PostEditType;
use App\Search\SearchPost;
use App\Service\Paginator;
use App\Form\SearchFormType;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminPostController extends AbstractController
{
    /**
     * Permet de récupérer des postes paginés
     * 
     * @Route("/admin/posts/{page<\d+>?1}", name="admin_posts_index")
     *
     * @param Request $request
     * @param Paginator $paginator
     * @param integer $page
     * @param PostRepository $repo
     * @return Response
     */
    public function index(Request $request, Paginator $paginator, int $page, PostRepository $repo): Response
    {   
        $search = new SearchPost();

        $form = $this->createForm(SearchFormType::class, $search);
        $form->handleRequest($request);

        /** @var Post[] contenant des utilisateurs paginés */
        $posts = $paginator
                    ->setParams([$repo, $page, $search->query, 'p', 'title'])
                    ->getData();
        
        $counts = $paginator->getCountPages();

        return $this->render('admin/post/index.html.twig', [
            'form'          => $form->createView(),
            'posts'         => $posts,
            'countPages'    => $counts,
            'page'          => $page
        ]);
    }

    /**
     * Permet d'éditer un poste par l'administrateur
     * 
     * @Route("/admin/post/{id}/edit", name="admin_post_edit", requirements={"id": "\d+"})
     *
     * @param Post $post
     * @param Request $request
     * @param ObjectManager $manager
     * @param Uploader $uploader
     * @return Response|null
     */
    public function edit(Post $post, Request $request, ObjectManager $manager, Uploader $uploader): ?Response
    {   
        $form = $this->createForm(PostEditType::class, $post);
        
        // Sauvegarder toutes les images de carousel
        $images = new ArrayCollection();
        if ($post->getImages() instanceof \Traversable):
            foreach ($post->getImages() as $img):
                $images->add($img);
            endforeach;
        endif;

        // Manipulation de données
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            // Suppression d'une ou plusieurs images de carousel
            foreach ($images as $img):
                if (!$post->getImages()->contains($img)):

                    $deleted = unlink(
                        $this->getParameter('carousel_directory') . DIRECTORY_SEPARATOR . $img->getImg()
                    );

                    if (!$deleted):
                        throw $this->createNotFoundException("Nous n'avons pas pû supprimer l'image {$img->getImg()} de carousel");
                    endif;

                    $manager->remove($img);
                    $manager->flush();
                endif;
            endforeach;

            /** @var UploadedFile $image contenant l'image de couverture */
            $image = $form['image']->getData();

            /** @var UploadedFile $video contenant la vidéo */
            $video = $form['video']->getData();

            // Suppression et upload d'un nouveau image de couverture
            if ($image && $image instanceof UploadedFile):
                $deleted = unlink($this->getParameter('images_directory') . DIRECTORY_SEPARATOR . $post->getImage());

                if (!$deleted):
                    throw $this->createNotFoundException("Nous n'avons pas pû supprimer l'image de couverture");
                endif;

                $fileNameImage = $uploader->upload(
                    $image, 
                    $this->getParameter('images_directory'), 
                    "Nous n'avons pas pû uploadé l'image de couverture, Réessayer"
                );
                $post->setImage($fileNameImage);
            endif;

            // Suppression et upload d'un nouveau vidéo
            if ($video && $video instanceof UploadedFile):
                $deleted = unlink($this->getParameter('videos_directory') . DIRECTORY_SEPARATOR . $post->getVideo());

                if (!$deleted):
                    throw $this->createNotFoundException("Nous n'avons pas pû supprimer la vidéo");
                endif;

                $fileNameVideo = $uploader->upload(
                    $video, 
                    $this->getParameter('videos_directory'), 
                    "Nous n'avons pas pû uploadé la vidéo, Réessayer"
                );
                $post->setVideo($fileNameVideo);
            endif;

            // Upload d'une ou plusieus images de carousel s'il y en a
            foreach ($post->getImages() as $key => $img):
                /** @var UploadedFile $carousel contenant l'image de carousel */
                $carousel = $form['images'][$key]['img']->getData();

                if ($carousel && $carousel instanceof UploadedFile):
                    $fileNameCarousel = $uploader->upload(
                        $carousel,
                        $this->getParameter("carousel_directory"),
                        "Nous n'avons pas pû uploadé cette image! Réssayer" 
                    );
                    // Ajout de nom de carousel
                    $img->setImg($fileNameCarousel);
                    // Sauvegarde de l'objet $img
                    $manager->persist($img);
                endif;
            endforeach;

            $manager->persist($post);
            $manager->flush();

            $this->addFlash(
                "success",
                "La modification de poste {$post->getTitle()} a été fait avec succès"
            );

            return $this->redirectToRoute('post_show', [
                'id'    => $post->getId(), 
                'slug'  => $post->getSlug()
            ], 301);

        endif;

        return $this->render("admin/post/edit.html.twig", [
            'form' => $form->createView(),
            'post' => $post
        ]);
    } 

    /**
     * Permet de supprimer un post
     *
     * @Route("/admin/post/{id}/remove", name="admin_post_delete", requirements={"id": "\d+"}) 
     *
     * @param Post $post
     * @param ObjectManager $manager
     * @return Response|null
     */
    public function remove(Post $post, ObjectManager $manager): ?Response
    {
        // Suppresion de carousel
        if ($post->getImages() instanceof \Traversable):
            foreach ($post->getImages() as $img):
                $deletedImg = unlink($this->getParameter('carousel_directory') . DIRECTORY_SEPARATOR . $img->getImg());

                if (!$deletedImg):
                    throw $this->createNotFoundException("Nous n'avons pas pû supprimer l'image  {$img->getCaption()}, cela empêche la suppression de {$post->getTitle()}");
                endif;
                $manager->remove($img);
            endforeach;
        endif;

        // Suppression de l'image de couverture
        $deletedImage = unlink($this->getParameter('images_directory') . DIRECTORY_SEPARATOR . $post->getImage());
        if (!$deletedImage):
            throw $this->createNotFoundException("Nous n'avons pas pû supprimer l'image {$post->getImage()}, cela empêche la suppression de {$post->getTitle()}");
        endif;

        // Suppression de la vidéo
        $deletedVideo = unlink($this->getParameter('videos_directory') . DIRECTORY_SEPARATOR . $post->getVideo());
        if (!$deletedVideo):
            throw $this->createNotFoundException("Nous n'avons pas pû supprimer la vidéo {$post->getVideo()}, cela empêche la suppression de {$post->getTitle()}");
        endif;
    
        $manager->remove($post);
        $manager->flush();

        $this->addFlash(
            'success',
            "La suppression de {$post->getTitle()} a été fait avec succès"
        );

        return $this->redirectToRoute("admin_posts_index");

    }

}
