<?php

namespace App\Controller\Dashboard\Admin;

use App\Controller\Dashboard\DashboardController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Response, RedirectResponse};

class AdminController extends DashboardController
{

    /**
     * @Route("/admin", name="admin_dashboard")
     * @return RedirectResponse|Response
     */
    public function indexAction()
    {
        return $this->redirectToRoute("admin_dashboard.user_list");
    }
}
