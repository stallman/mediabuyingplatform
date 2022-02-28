<?php


namespace App\Service;


use App\Entity\CurrencyList;
use App\Entity\CurrencyRate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Yaml\Yaml;

class CurrencyConverter
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param float|null $price
     * @param UserInterface $user
     * @param int $precision
     * @return float
     */
    public function convertToUserCurrency(?float $price, UserInterface $user, int $precision = 4)
    {
        $basicSettingsConfigFile = $this->getConfigFile('basic_settings_config');

        $systemDefaultCurrencyId = $basicSettingsConfigFile['parameters']['default_currency'];
        $userDefaultCurrencyId = $user->getUserSettingsBySlug('default_currency')->getValue();

        if ($systemDefaultCurrencyId == $userDefaultCurrencyId) {
            return $price;
        }

        $price = $this->convertCurrencies($userDefaultCurrencyId, $price, $precision);

        return $price;
    }

    /**
     * @param float|null $price
     * @param UserInterface $user
     * @param int $precision
     * @param CurrencyList|null $ruble
     * @return float
     */
    public function convertRubleToUserCurrency(?float $price, UserInterface $user, int $precision = 4, ?CurrencyList $ruble = null)
    {
        if(!$ruble){
            $ruble = $this->entityManager->getRepository(CurrencyList::class)->getByIsoCode('rub');
        }
        $userDefaultCurrencyId = $user->getUserSettingsBySlug('default_currency')->getValue();

        if ($ruble->getId() == $userDefaultCurrencyId) {
            return $price;
        }

        $price = $this->convertCurrencies($userDefaultCurrencyId, $price, $precision);

        return $price;
    }

    /**
     * @param UserInterface $user
     * @return CurrencyList|object|null
     */
    public function getUserCurrency(UserInterface $user)
    {
        $currencyId = $user->getUserSettingsBySlug('default_currency')->getValue();

        return $this->entityManager->getRepository(CurrencyList::class)->findOneBy(['id' => $currencyId]);

    }

    /**
     * @param int $userDefaultCurrencyId
     * @param float|null $price
     * @param int $precision - количество знаков после запятой
     * @return float
     */
    public function convertCurrencies(int $userDefaultCurrencyId, ?float $price, int $precision = 4, string $date = '')
    {
        $price = floatval($price);

        $userDefaultCurrency = $this->getCurrency($userDefaultCurrencyId);
        $isoCode = $userDefaultCurrency->getIsoCode();

        if($isoCode != 'rub'){
            $currencyRate = $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode($isoCode, $date);
            $price = $price * $currencyRate;
        }

        return round($price, $precision);
    }

    /**
     * @param int $id
     * @return CurrencyList|object|null
     */
    private function getCurrency(int $id)
    {
        return $this->entityManager->getRepository(CurrencyList::class)->findOneBy(['id' => $id]);
    }

    public function getCurrencyByCode(?string $code = null): ?CurrencyList
    {
        $currency = null;

        if (null !== $code){
            /** @var CurrencyList|null $currency */
            $currency = $this->entityManager->getRepository(CurrencyList::class)
                ->findOneBy(['iso_code' => $code]);
        }

        return $currency;
    }

    /**
     * @param string $parameterName
     * @return mixed
     */
    private function getConfigFile(string $parameterName)
    {
        $basicSettingsConfigPath = $this->parameterBag->get($parameterName);

        return Yaml::parseFile($basicSettingsConfigPath);
    }
}