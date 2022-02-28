<?php

namespace App\Controller\Front\Postback;

use App\Entity\Conversions;
use App\Entity\CurrencyList;
use App\Entity\Partners;
use App\Entity\Postback;
use App\Entity\TeasersClick;
use App\Service\CurrencyConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ConversionStatus;
use App\Entity\Country;

class PostbackController
{
    public EntityManagerInterface $entityManager;
    public Request $request;
    public LoggerInterface $logger;
    public CurrencyConverter $currencyConverter;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger,
                                CurrencyConverter $currencyConverter
    ) {
        $this->request = Request::createFromGlobals();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->currencyConverter = $currencyConverter;
    }
    /**
     * @Route("/postback", name="front.postback", methods={"POST", "GET"})
     * @return JsonResponse
     */
    public function postBackAction()
    {
        $postBack = $this->request->query->get('postback');
        $ppid = $this->request->query->get('ppid');
        $click = $this->request->query->get('click_id');
        $status = $this->request->query->get('status');
        $currency = $this->request->query->get('currency');
        $payout = $this->request->query->get('payout');
        $fullData = $this->request->query;

        $isNotExist= $this->checkExistParams($postBack, $ppid, $click, $status, $currency, $payout);
        if($isNotExist) return $isNotExist;

        $ppid = $this->entityManager->getRepository(Partners::class)->find($ppid);
        $click = $this->entityManager->getRepository(TeasersClick::class)->findOneBy(['id' => Uuid::fromString($click)]);
        $currency = $this->entityManager->getRepository(CurrencyList::class)->getByIsoCode($currency);

        $isNotExist= $this->checkExistEntryByParams($ppid, $click, $currency);
        if($isNotExist) return $isNotExist;

        $status = $this->getStatus($ppid, $status);
        if($status instanceof JsonResponse) return $status;
 
        $conversion = $this->entityManager->getRepository(Conversions::class)->findOneBy(['teaserClick' => $click]);

        if (empty($conversion)) {
            $conversion = new Conversions();
            $conversion->setMediabuyer($click->getBuyer());
            $conversion->setTeaserClick($click);
            $conversion->setAffilate($ppid);
            $conversion->setSource($click->getSource());
            $conversion->setNews($click->getNews());
            $conversion->setSubgroup($click->getTeaser()->getTeasersSubGroup());
            $conversion->setCountry($this->entityManager->getRepository(Country::class)->findOneBy(['iso_code' => $click->getCountryCode()]));
            $conversion->setCurrency($currency);
            $conversion->setDesign($click->getDesign());
            $conversion->setAlgorithm($click->getAlgorithm());
            $conversion->setAmount($payout);
            $conversion->setAmountRub($this->currencyConverter->convertCurrencies($currency->getId(), floatval($payout)));
            $conversion->setUuid($click->getUuid());
            $conversion->setStatus($this->entityManager->getRepository(ConversionStatus::class)->findOneBy(['label_en' => $status]));
            $conversion->setCreatedAt(null);
            $conversion->setUpdatedAt(null);
            $this->entityManager->persist($conversion);
        }
        $conversion->setStatus($this->entityManager->getRepository(ConversionStatus::class)->findOneBy(['label_en' => $status]));
        $this->entityManager->flush();

        return $this->savePostBack($ppid, $click, $status, $currency, $payout, $fullData);
    }

    private function checkExistParams($postBack, $ppid, $click, $status, $currency, $payout)
    {
        if(is_null($postBack)){
            return JsonResponse::create('postback parameter not found', 422);
        }
        if(is_null($ppid)){
            return JsonResponse::create('ppid parameter not found', 422);
        }
        if(is_null($click)){
            return JsonResponse::create('clickId parameter not found', 422);
        }
        if(is_null($status)){
            return JsonResponse::create('status parameter not found', 422);
        }
        if(is_null($currency)){
            return JsonResponse::create('currency parameter not found', 422);
        }
        if(is_null($payout)){
            return JsonResponse::create('payout parameter not found', 422);
        }
    }

    private function checkExistEntryByParams(?Partners $ppid, ?TeasersClick $click, ?CurrencyList $currency)
    {
        if(is_null($ppid)){
            return JsonResponse::create('Affiliate not found', 404);
        }
        if(is_null($click)){
            return JsonResponse::create('Click not found', 404);
        }
        if(is_null($currency)){
            return JsonResponse::create('Currency not found', 404);
        }
    }

    private function getStatus(Partners $affilate, string $status)
    {
        if($affilate->getStatusDeclined() == $status){
            return 'declined';
        }
        if($affilate->getStatusPending() == $status){
            return 'pending';
        }
        if($affilate->getStatusApproved() == $status){
            return 'approved';
        }

        return JsonResponse::create('Status not found', 404);
    }

    private function savePostBack(Partners $affilate, TeasersClick $click, string $status, CurrencyList $currency, string $payout, $fullData)
    {
        try {
            $postBack = new Postback();
            $postBack->setAffiliate($affilate)
                ->setClick($click)
                ->setStatus($status)
                ->setCurrencyCode($currency->getIsoCode())
                ->setPayout($payout)
                ->setFulldata($fullData->all());
                
            $postBack->setPayoutRub($this->getPostbackRub($postBack));

            $this->entityManager->persist($postBack);
            $this->entityManager->flush();

            $this->logger->log('alert', "Save postback");

            return JsonResponse::create('success', 200);
        } catch (\Exception $exception) {
            $this->logger->error("An error occurred while saving postback {$exception->getMessage()}");

            return JsonResponse::create($exception->getMessage(), 500);
        }
    }

    private function getPostbackRub(Postback $postback): ?string
    {
        if($postback->getCurrencyCode() == 'rub') return $postback->getPayout();

        $currency = $this->currencyConverter->getCurrencyByCode($postback->getCurrencyCode());

        $payout = $postback->getPayout();

        if (null !== $currency) {
            $payout = $this->currencyConverter->convertCurrencies($currency->getId(), floatval($postback->getPayout()));
        }

        return strval($payout);
    }
}