<?php

namespace App\Controller\Dashboard\MediaBuyer;

use App\Controller\Dashboard\DashboardController;
use App\Traits\Dashboard\DesignActionTrait;
use App\Traits\Dashboard\AlgorithmActionTrait;
use Symfony\Component\Routing\Annotation\Route;

class SplitTestController extends DashboardController
{
    use DesignActionTrait;
    use AlgorithmActionTrait;

    /**
     * @Route("/mediabuyer/split-tests/design/list", name="mediabuyer_dashboard.split_tests.design_list")
     */
    public function designListAction()
    {
        return $this->designList();
    }

    /**
     * @Route("/mediabuyer/split-tests/algorithm/list", name="mediabuyer_dashboard.split_tests.algorithm_list")
     */
    public function algorithmListAction()
    {
       return $this->algorithmList();
    }

    /**
     * @Route("/mediabuyer/split-tests/algorithm/active", name="mediabuyer_dashboard.split_tests.algorithm_active", methods={"POST"})
     */
    public function algorithmBuyerActiveAction()
    {
       return $this->algorithmActive($this->request->request->get('data'));
    }

    /**
     * @Route("/mediabuyer/split-tests/design/active", name="mediabuyer_dashboard.split_tests.design_active", methods={"POST"})
     */
    public function designBuyerActiveAction()
    {
       return $this->designActive($this->request->request->get('data'));
    }

}
