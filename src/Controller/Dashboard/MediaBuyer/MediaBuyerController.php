<?php

namespace App\Controller\Dashboard\MediaBuyer;

use App\Controller\Dashboard\DashboardController;
use App\Entity\Teaser;
use Symfony\Component\HttpFoundation\{RedirectResponse, Response};
use Symfony\Component\Routing\Annotation\Route;

class MediaBuyerController extends DashboardController
{
    /**
     * @Route("/mediabuyer", name="mediabuyer_dashboard")
     * @return RedirectResponse|Response
     */
    public function indexAction()
    {
        return $this->redirectToRoute("mediabuyer_dashboard.teaser_list");
    }
}