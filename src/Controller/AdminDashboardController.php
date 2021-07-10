<?php

namespace App\Controller;

use App\Service\Dashboard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDashboardController extends AbstractController
{
    /**
     * Sert à créer un tableau de bord
     * 
     * @Route("/admin/dashboard", name="admin_dashboard")
     * 
     * @param Dashboard $dashboard
     * @return Response
     */
    public function index(Dashboard $dashboard): Response
    {
        // Best
        $best   = $dashboard->getBestOrWorst('DESC');
        // Worst
        $worst  = $dashboard->getBestOrWorst('ASC');
        // Last posts and users
        $last   = $dashboard->getLast(5);
        // Counts
        $counts = $dashboard->getCounts();

        return $this->render('admin/dashboard/index.html.twig', [
            'best'      => $best,
            'worst'     => $worst,
            'last'      => $last,
            'counts'    => $counts
        ]);
    }
}
