<?php


namespace App\Controller\Dashboard\Journalist;


use Symfony\Component\Routing\Annotation\Route;

class IndexController extends JournalistController
{
    /**
     * @Route("/journalist", name="journalist_dashboard")
     */
    public function indexAction()
    {
        return $this->redirectToRoute("journalist_dashboard.news_list");
    }

}