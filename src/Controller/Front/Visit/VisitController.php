<?php

namespace App\Controller\Front\Visit;

use App\Controller\Front\FrontController;
use App\Entity\Visits;
use Symfony\Component\HttpFoundation\{JsonResponse};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;

class VisitController extends FrontController
{
    /**
     * @Route("/visit/{uuid}", name="front.visit")
     * @ParamConverter("visit", class="App\Entity\Visits")
     * @param Visits $visit
     * @return JsonResponse
     */
    public function visitAction(Visits $visit)
    {
        try {
            $visit->setScreenSize($this->request->request->get('screenSize'));

            $this->entityManager->flush();

            return JsonResponse::create('', 200);
        } catch (\Exception $exception) {

            return JsonResponse::create('', 500);
        }
    }
}