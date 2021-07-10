<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Search\SearchPost;
use App\Service\Paginator;
use App\Form\SearchFormType;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    /**
     * Sert à récupérer tous les commentaires
     * 
     * @Route("/admin/comments/{page<\d+>?1}", name="admin_comments_index")
     *
     * @param Request $request
     * @param Paginator $paginator
     * @param integer $page
     * @param CommentRepository $repo
     * @return Response
     */
    public function index(Request $request, Paginator $paginator, int $page, CommentRepository $repo): Response
    {
        $search = new SearchPost();

        $form = $this->createForm(SearchFormType::class, $search);
        $form->handleRequest($request);

        /** @var Comment[] contenant des utilisateurs paginés */
        $comments = $paginator
                        ->setParams([$repo, $page, $search->query, 'c', 'content'])
                        ->getData();
        
        $counts = $paginator->getCountPages();
                
        return $this->render('admin/comment/index.html.twig', [
            'form'          => $form->createView(),
            'comments'      => $comments,
            'page'          => $page,
            'countPages'    => $counts 
        ]);
    }

    /**
     * Sert à modifier le commentaire demandé
     * 
     * @Route("/admin/comment/{id<\d+>}/edit", name="admin_comment_edit")
     *
     * @param Comment $comment
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function edit(Comment $comment, Request $request, ObjectManager $manager): Response
    {   
        // CRéation de formulaire
        $form = $this->createForm(CommentType::class, $comment);

        // Manipulation de la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                'success',
                "Vous avez été modifié le commentaire de {$comment->getAuthor()->getFullName()} avec succès"
            );

            return $this->redirectToRoute('admin_comments_index', [], 301);
        endif;

        return $this->render('admin/comment/edit.html.twig', [
            'form'      => $form->createView(),
            'comment'   => $comment
        ]);
    }

    /**
     * Sert à supprimer un commentaire
     * 
     * @Route("/admin/comment/{id<\d+>}/remove", name="admin_comment_delete")
     *
     * @param Comment $comment
     * @param ObjectManager $manager
     * @return Response
     */
    public function delete(Comment $comment, ObjectManager $manager): Response
    {
        // Suppression
        $manager->remove($comment);
        $manager->flush();

        $this->addFlash(
            'success',
            "Vous avez été supprimé le commentaire de {$comment->getAuthor()->getFullName()} avec succès"
        );

        return $this->redirectToRoute("admin_comments_index", [], 301);
    }
}
