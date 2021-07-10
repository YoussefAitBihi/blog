<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Entity\Comment;
use App\Entity\PostLike;
use App\Form\SearchFormType;
use App\Form\CommentType;
use App\Service\Uploader;
use App\Form\PostEditType;
use App\Search\SearchPost;
use App\Service\Paginator;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController 
{
    /**
     * Permet d'afficher tous les postes
     * 
     * @Route("/posts/{page<\d+>?1}", name="posts_index")
     * 
     * @param Paginator $paginator
     * @param integer $page
     * @param Request $request
     * @return Response
     */
    public function index(Paginator $paginator, int $page, Request $request, PostRepository $repo): Response 
    {
        $search = new SearchPost();

        $form = $this->createForm(SearchFormType::class, $search);

        $form->handleRequest($request);

        /** @var Post[] $posts contenant les postes paginés */
        $posts = $paginator
                    ->setParams([$repo, $page, $search->query, 'p', 'title'])
                    ->getData();

        return $this->render('post/index.html.twig', [
            'posts'         => $posts,
            'countPages'    => $paginator->getCountPages(),
            'page'          => $page,
            'form'          => $form->createView()
        ]);
    }

    /**
     * Permet de créer un nouveau poste
     * 
     * @Route("/post/new", name="post_new")
     * 
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param Uploader $upload
     * @param ObjectManager $manager
     * @return Response
     */
    public function new(Request $request, Uploader $upload, ObjectManager $manager): Response 
    {
        // Nouveau poste
        $post = new Post();
        // L'utilisateur connecté
        $user = $this->getUser();
        // Création de formulaire
        $form = $this->createForm(PostType::class, $post);

        // Manipulation de données
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            /** @var UploadedFile $image contenant l'image */
            $image = $form['image']->getData();

            /** @var UploadedFile $video contenant la vidéo */
            $video = $form['video']->getData();

            // Le nom de l'image
            $fileNameImage = $upload->upload(
                $image, 
                $this->getParameter('images_directory'), 
                "L'image n'a pas été posté ! Veuillez réssayer ultérieurement"
            );

            // Le nom de la vidéo
            $fileNameVideo = $upload->upload(
                $video, 
                $this->getParameter('videos_directory'), 
                "La vidéo n'a pas été posté ! Veuillez réssayer ultérieurement"
            );

            // Ajouter l'image, la vidéo et le créateur de poste
            $post
                ->setImage($fileNameImage)
                ->setVideo($fileNameVideo)
                ->setAuthor($user);

            // La carousel
            foreach ($form['images']->getData() as $key => $image):

                /** @var UploadedFile $img contenant l'image de carousel */
                $img = $form['images'][$key]['img']->getData();

                // Get FileName
                $fileNameImg = $upload->upload(
                    $img,
                    $this->getParameter('carousel_directory'),
                    "L'image de carousel n'a pas été uploadé, Réessayer ultérieurement"
                );
                // Add Image
                $image
                    ->setImg($fileNameImg)
                    ->setPost($post);

                $manager->persist($image);

            endforeach;

            $manager->persist($post);
            $manager->flush();

            $this->addFlash("success", "Monsieur {$user->getFullName()}, votre poste {$post->getTitle()}a été publié avec succès");

            return $this->redirectToRoute('post_show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug()
            ], 301);

        endif;

        return $this->render('post/new.html.twig', [
            'form'      => $form->createView()
        ]);
    }

    /**
     * Permet de faire un like ou l'enlever d'un poste préféré
     * 
     * @Route("/post/{id}/like", name="post_like")
     *
     * @param ObjectManager $manager
     * @param Post $post
     * @param PostLikeRepository $repo
     * @return JsonResponse
     */
    public function like(ObjectManager $manager, Post $post, PostLikeRepository $repo): JsonResponse
    {
        $user = $this->getUser();

        // Si L'utilisateur n'étant pas connecté
        if (!$user):
            return $this->json([
                "Message d'echec"   => "Veuillez vous connecter afin de pouvoir faire un like",
                "code"              => 403,
            ], 403);
        endif;

        // Si l'utilisateur étant déja fait un like
        $liked = $post->hasLike($user);

        if ($liked):
            $like = $repo->findOneBy([
                'postLike' => $post->getId(),
                'userLike' => $user->getId()
            ]);

            $manager->remove($like);
            $manager->flush();

            return $this->json([
                "Message de sussès" => "Vous avez enlevé le like",
                "code"              => 200,
                'count'             => $repo->count(['postLike' => $post])
            ], 200);

        // Si l'utilisateur veut faire un like
        else:
            $like = new PostLike();

            $like
                ->setPostLike($post)
                ->setUserLike($user);

            $manager->persist($like);
            $manager->flush();

            return $this->json([
                "Message de succès" => "Monsieur {$user->getFullName()}Vous avez fait un like pour {$post->getTitle()}",
                "code"              => 200,
                'count'             => $repo->count(['postLike' => $post])
            ], 200);

        endif;

    }

    /**
     * Permet d'afficher un poste via son slug et son id
     * 
     * @Route("/post/{id}-{slug}", name="post_show")
     *
     * @param Post $post
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function show(Post $post, Request $request, ObjectManager $manager): Response 
    {
        // L'utilisateur connecté
        $user = $this->getUser() ?? null;

        // Get User Infos
        if ($user):
            
            $user = $this->getUser();
            // True s'il y avait un ou plusieurs commentaire ou le contraire
            $hasComment = $post->hasCommentPost($user);

            if (!$hasComment && $post->getAuthor() !== $user):
                // Nouvelle Comment
                $comment = new Comment();

                // Le Formulaire
                $form = $this->createForm(CommentType::class, $comment);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()):
                    $comment->setPost($post)
                            ->setAuthor($user);

                    $manager->persist($comment);
                    $manager->flush();

                    $this->addFlash('success', 'Vous avez été posté un commentaire avec succès');

                    return $this->redirectToRoute('post_show', [
                        'id'    => $post->getId(),
                        'slug'  => $post->getSlug()
                    ], 301);
                    
                endif;
            endif;

        endif;

        return $this->render('post/show.html.twig', [
            'post'      => $post,
            'form'      => ($user && !$hasComment && $post->getAuthor() !== $user) ? $form->createView() : null
        ]);
    }

    /**
     * Permet d'éditer un poste via son id et son slug
     * 
     * @Route("/post/{id}-{slug}/edit", name="post_edit")
     * 
     * @Security("is_granted('ROLE_USER') and post.getAuthor() == user")
     *
     * @param Post $post
     * @param Request $request
     * @param Uploader $uploader
     * @param ObjectManager $manager
     * @return Response
     */
    public function edit(Post $post, Request $request, Uploader $uploader, ObjectManager $manager): Response 
    {
        // Sauvegarde les images de carousel
        $originalImages = new ArrayCollection();

        foreach ($post->getImages() as $img):
            $originalImages->add($img);
        endforeach;

        $form = $this->createForm(PostEditType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            // Suppression du Sous-Form (Carousel)
            foreach ($originalImages as $img):
                if (!$post->getImages()->contains($img)):
                    $deleted = unlink($this->getParameter('carousel_directory').'/'.$img->getImg());
                    if ($deleted):
                        $manager->remove($img);
                    endif;
                endif;
            endforeach;

            /** @var UploadedFile $image contenant l'image */
            $image = $form['image']->getData();

            /** @var UploadedFile $video contenant la vidéo */
            $video = $form['video']->getData();

            // Vérifier qu'il y ait un fichier de type UploadedFile pour le carousel
            $hasImage = false;
            foreach ($post->getImages() as $key => $imagee):
                if ($form['images'][$key]['img']->getData() instanceof UploadedFile):
                    $hasImage = true;
                break;
                endif;
            endforeach;

            // Si l'ultilisateur décidant à modifier l'image ou la vidéo ou l'image de carousel
            if ($image || $video || $hasImage):
                
                // Si l'utilisateur ayant été modifié l'image
                if ($image):
                    // Suppression dévinitive de l'image
                    $deleted = unlink($this->getParameter('images_directory').'/'.$post->getImage());

                    if ($deleted):
                        // Le nom de l'image
                        $fileNameImage = $uploader->upload(
                            $image, 
                            $this->getParameter('images_directory'), 
                            "L'image n'a pas été posté ! Veuillez réssayer ultérieurement"
                        );
                        $post->setImage($fileNameImage);
                    endif;
                endif;

                // Si l'utilisateur a été modifié la vidéo
                if ($video):
                    $deleted = unlink($this->getParameter('videos_directory').'/'.$post->getVideo());

                    if ($deleted):
                        // Le nom de l'image
                        $fileNameVideo = $uploader->upload(
                            $video, 
                            $this->getParameter('videos_directory'), 
                            "L'image n'a pas été posté ! Veuillez réssayer ultérieurement"
                        );
                        $post->setVideo($fileNameVideo);
                    endif;
                endif;

                // Si l'utilisateur ayant modifié ou ajouté une image de carousel
                if ($hasImage):

                    foreach ($post->getImages() as $key => $imgCa):

                        /** @var UploadedFile $imgCarousel contenant l'image de carousel */
                        $imgCarousel = $form['images'][$key]['img']->getData();

                        if ($imgCarousel):

                            // Suppression définitive de carousel
                            if ($originalImages[$key]->getImg()):
                                $deleted = unlink($this->getParameter('carousel_directory').'/'.$originalImages[$key]->getImg());
                            endif;

                            // Si la suppression de carousel a été fait avec succès
                            if ($deleted):
                                $fileNameCarousel = $uploader->upload(
                                    $imgCarousel, 
                                    $this->getParameter('carousel_directory'),
                                    "L'image n'a pas été uplodé. Réessayer ultérieurement"
                                );
    
                                $imgCa
                                    ->setImg($fileNameCarousel)
                                    ->setPost($post);
    
                                $manager->persist($imgCa);
                            endif;
                        endif;
                    endforeach;
                endif;
            endif;

            // Persister l'objet $post et l'enregistrer
            $manager->persist($post);
            $manager->flush();
            
            $this->addFlash("success", "L'article {$post->getTitle()} a été modifié avec succès");

            return $this->redirectToRoute('post_show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug()
            ], 301);

        endif;

        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post
        ]);

    }

    /**
     * Permet de supprimer un poste
     * 
     * @Route("/post/{id}-{slug}/remove", name="post_delete")
     * 
     * @Security("is_granted('ROLE_USER') and post.getAuthor() == user")
     *
     * @param ObjectManager $manager
     * @param Post $post
     * @param Request $request
     * @return Response
     */
    public function remove(ObjectManager $manager, Post $post, Request $request): Response 
    {
        // Suppression du caroussel
        foreach ($post->getImages() as $image):
            $deleted = unlink($this->getParameter('carousel_directory').'/'.$image->getImg());
            if ($deleted):
                $manager->remove($image);
            endif;
        endforeach;

        // Suppression de la vidéo
        unlink($this->getParameter('videos_directory').'/'.$post->getVideo());
        // Suppression de l'image
        unlink($this->getParameter('images_directory').'/'.$post->getImage());

        $manager->remove($post);
        $manager->flush();

        $this->addFlash('success', "L'article {$post->getTitle()} a été supprimé");

        $referer = $request->get('referer');
        
        if ($referer):
            return $this->redirect($referer, 301);
        endif;

        return $this->redirectToRoute('posts_index', [], 301);
    }

}
