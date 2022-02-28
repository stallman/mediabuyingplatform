<?php

namespace App\Controller\Dashboard\MediaBuyer;

use App\Controller\Dashboard\DashboardController;
use App\Entity\DomainParking;
use App\Service\CertbotEncrypt;
use App\Service\CronHistoryChecker;
use App\Traits\Dashboard\DomainParkingTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DomainController extends DashboardController
{
    use DomainParkingTrait;

    /**
     * @Route("/mediabuyer/domain/list", name="mediabuyer_dashboard.domain_list")
     */
    public function listAction()
    {
        $domains = $this->entityManager->getRepository(DomainParking::class)->getMediaBuyerDomainsList($this->getUser());
        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $lastCronTime = $cronHistoryChecker->getLastCronTime('letsencrypt-generate');

        return $this->render('dashboard/mediabuyer/domain/list.html.twig', [
            'columns' => $this->getDomainParkingTableHeader(),
            'domains' => $domains,
            'ip' => $this->request->server->get('SERVER_ADDR'),
            'host' => $this->request->server->get('HTTP_HOST'),
            'h1_header_text' => 'Все домены',
            'new_button_label' => 'Добавить домен',
            'new_button_action_link' => $this->generateUrl('mediabuyer_dashboard.domain_add'),
            'cron_date' => ($lastCronTime) ? $lastCronTime->setTimezone('Europe/Moscow') : null,
        ]);
    }

    /**
     * @Route("/mediabuyer/domain/add", name="mediabuyer_dashboard.domain_add")
     */
    public function addAction(CertbotEncrypt $certbot)
    {
        $form = $this->createDomainParkingForm(null);
        if($form->isSubmitted()){
            /** @var DomainParking $formData */
            $formData = $form->getData();
            /** @var DomainParking $domain */
            [$result, $domain] = $this->getDelDomain($formData->getDomain());
            if($result) {
                $domain->setIsDeleted(false)
                    ->setCertEndDate(null);

                $errMsg = $certbot->letsEncrypt($domain->getDomain());
                if ($errMsg) {
                    $domain->setErrorMessage($errMsg);
                }
                $this->entityManager->flush();
                $this->addFlash('succes', $this->getFlashMessage('domain_create'));
                return $this->redirectToRoute('mediabuyer_dashboard.domain_list', []);
            }
            if($form->isValid()){
                $formData->setUser($this->getUser());
                $errMsg = $certbot->letsEncrypt($formData->getDomain());
                if ($errMsg) {
                    $formData->setErrorMessage($errMsg);
                }
                $this->entityManager->persist($formData);
                $this->entityManager->flush();
                $this->addFlash('succes', $this->getFlashMessage('domain_create'));
                return $this->redirectToRoute('mediabuyer_dashboard.domain_list', []);
            }
        }

        return $this->render('dashboard/mediabuyer/domain/form.html.twig', [
            'h1_header_text' => 'Добавить домен',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param DomainParking $domain
     * @Route("/mediabuyer/domain/edit/{id}", name="mediabuyer_dashboard.domain_edit")
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editAction(DomainParking $domain, CertbotEncrypt $certbot)
    {
        $form = $this->createDomainParkingForm($domain);
        if($form->isSubmitted() && $form->isValid()){
            if($this->domainWasChanged($domain, $form)){
                /** @var DomainParking $domainParking */
                $domainParking = $form->getData();
                $domainParking->setCertEndDate(null);

                $errMsg = $certbot->letsEncrypt($domain->getDomain());
                if ($errMsg) {
                    $domainParking->setErrorMessage($errMsg);
                }

                $this->entityManager->persist($domainParking);
            }

            $this->entityManager->flush();
            $this->addFlash('succes', $this->getFlashMessage('domain_edit'));

            return $this->redirectToRoute('mediabuyer_dashboard.domain_list', []);
        }

        return $this->render('dashboard/mediabuyer/domain/form.html.twig', [
            'h1_header_text' => 'Редактировать домен',
            'form' => $form->createView(),
        ]);
    }

    private function domainWasChanged($domain, $form)
    {
        return $this->getOldDomain($domain->getId()) !== $form->getData()->getDomain();
    }

    private function getOldDomain($id)
    {
        $query = $this->entityManager->createQueryBuilder('dp')
            ->select('dp.domain')
            ->from(DomainParking::class, 'dp')
            ->where('dp.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        return $query->getSingleScalarResult();
    }

    /**
     * @Route("/mediabuyer/domain/active-main/{id}", name="mediabuyer_dashboard.domain_active_main")
     * @param DomainParking $domain
     * @return RedirectResponse|Response
     */
    public function activeMainAction(DomainParking $domain)
    {
        $this->activeMainDomain($domain);

        return $this->redirectToRoute("mediabuyer_dashboard.domain_list");
    }

    /**
     * @Route("/mediabuyer/domain_parking/delete/{id}", name="mediabuyer_dashboard.domain_parking_delete")
     * @param DomainParking $domainParking
     * @return JsonResponse
     */
    public function deleteAction(DomainParking $domainParking, CertbotEncrypt $certbot)
    {
        try{
            if($domainParking->getIsMain()){
                $this->addFlash('error', $this->getFlashMessage('domain_delete_error'));
            } else {
                $domainParking->setIsDeleted(true);
                $certbot->removeNginxCfg($domainParking->getDomain());
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('domain_delete_is_main_error'));
            }

            return JsonResponse::create('', 200);
        } catch(\Exception $exception) {

            return JsonResponse::create('Ошибка при удалении домена: ' . $exception->getMessage(), 500);
        }
    }
}
