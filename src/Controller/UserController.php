<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Search\SearchPost;
use App\Service\Paginator;
use App\Form\SearchFormType;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    /**
     * Permet d'afficher un utilisateur via son 
     * 
     * @Route("/user/{id}-{slug}/{page<\d+>?1}", name="user_show")
     * 
     * @param User $user
     * @param Paginator $paginator
     * @param int $page
     * @return Response
     */
    public function index(User $user, Request $request, Paginator $paginator, int $page, PostRepository $repo): Response
    {
        $search = new SearchPost();

        $form = $this->createForm(SearchFormType::class, $search);
        $form->handleRequest($request);

        /** @var Post[] contenant des postes paginés */
        $posts = $paginator
                    ->setParams([$repo, $page, $search->query, 'p', 'title'])
                    ->getData($user);

        return $this->render('user/index.html.twig', [
            'user'          => $user,
            'form'          => $form->createView(),
            'posts'         => $posts,
            'page'          => $page,
            'countPages'    => $paginator->getCountPages($user)
        ]);
    }
}
