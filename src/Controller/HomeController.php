<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * Get Home Page
     * 
     * @Route("/", name="homepage")
     *
     * @param UserRepository $repoUser
     * @param PostRepository $repoPost
     * @return Response
     */
    public function index(UserRepository $repoUser, PostRepository $repoPost): Response 
    {
        /** @var array $users contenant les 4 derniers inscrivants */
        $users = $repoUser->getTopUsers();
        
        /** @var array $posts contenant les 4 derniers postes */
        $posts = $repoPost->getTopPosts();

        return $this->render('home/index.html.twig', [
            'users' => $users,
            'posts' => $posts
        ]);
    }
}
