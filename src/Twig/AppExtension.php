<?php


namespace App\Twig;


use App\Entity\DomainParking;
use App\Service\CurrencyConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    public EntityManagerInterface $entityManager;
    public Environment $twigEnvironment;
    public Yaml $yaml;
    public Container $container;
    public CurrencyConverter $currencyConverter;
    private RouterInterface $router;

    public function __construct(EntityManagerInterface $entityManager,
                                Environment $twigEnvironment,
                                Yaml $yaml,
                                Container $container,
                                CurrencyConverter $currencyConverter,
                                RouterInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->twigEnvironment = $twigEnvironment;
        $this->yaml = $yaml;
        $this->container = $container;
        $this->currencyConverter = $currencyConverter;
        $this->router = $router;
    }

    /**
     * @param string $routName
     * @return mixed
     */
    public function getConfig(string $routName)
    {
        $routNameArray = explode('.', $routName);
        $dashboardPrefix = $routNameArray[0];
        $dashboardPrefixArray = explode('_', $dashboardPrefix);
        $dashboardName = $dashboardPrefixArray[0];
        $configFileFormat = '%s.yaml';
        $configFile = sprintf($configFileFormat, $dashboardName);
        $config = $this->yaml::parseFile($this->container->getParameter('dashboard_config') . DIRECTORY_SEPARATOR . $configFile);

        return $config;
    }

    public function getConfigBySection(string $routName)
    {
        $routNameArray = explode('.', $routName);
        
        if (count($routNameArray) > 2 ) {
            return $this->getConfigBySectionPatch($routName, $routNameArray);
        }

        $dashboardSuffix = $routNameArray[1];
        $dashboardSuffixArray = explode('_', $dashboardSuffix);
        $config = false;
            
        if (isset($dashboardSuffixArray[1]) && isset($this->getConfig($routName)[$dashboardSuffixArray[1]][$dashboardSuffixArray[0]])) {
            $config = $this->getConfig($routName)[$dashboardSuffixArray[1]][$dashboardSuffixArray[0]];
        }

        return $config;
    }

    //оригинальный метод getConfigBySection() не даёт возможности работать с конфигами роутинга с глубокой вложенностью, например, mediabuyer_dashboard.statistic.traffic_analysis_list 
    //Но удалять его не рекомендуется, т.к. на нём завязана часть работы сайта (например, ajax загрузка тизеров)
    private function getConfigBySectionPatch($routName, $routNameArray)
    {
        $configPostfix = $this->getConfigPostfix($routNameArray);
        $configPrefix = $this->getConfigPrefix($routNameArray, $configPostfix);

        unset($routNameArray[0]);
        unset($routNameArray[array_key_last($routNameArray)]);

        $resultArray[] = $configPostfix;

        foreach ($routNameArray as $routNameEl) {
            $resultArray[] = $routNameEl;
        }

        $resultArray[] = $configPrefix;

        $config = $this->getConfig($routName);

        for ($i=0; $i < count($resultArray); $i++) {
            if ($i == 0) {
                $result = $config;
            }
            if (isset($result[$resultArray[$i]])) {
                $result = $result[$resultArray[$i]];
            }
            
        }
        
        return $result; 
    }

    private function getConfigPostfix($routNameArray)
    {
        $lastElement = $routNameArray[array_key_last($routNameArray)];
        $configPostfix = explode('_', $lastElement);
        return $configPostfix[array_key_last($configPostfix)];
    }

    private function getConfigPrefix($routNameArray, $configPostfix)
    {
        $lastElement = $routNameArray[array_key_last($routNameArray)];
        return str_replace('_' . $configPostfix, '', $lastElement);
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderSendPulseScript()
    {
        return $this->generateSendPulseScript();
    }

    private function getDomainSendPulseId()
    {
        $domainParking = $this->entityManager->getRepository(DomainParking::class)->findBy(['domain' =>  $this->cleanDomain($_SERVER['HTTP_HOST'])]);
        return (count($domainParking) > 0) ? $domainParking[0]->getSendPulseId() : "";
    }

    private function cleanDomain($domain)
    {
        $domain = $this->removeProtocol($domain);
        $domain = $this->removeSlash($domain);

        return $domain;
    }

    private function generateSendPulseScript()
    {
        if ($this->getDomainSendPulseId()) {
            return '<script charset="UTF-8" src="//web.webpushs.com/js/push/' . $this->getDomainSendPulseId() . '.js" async></script>';
        }
        return "";
    }

    private function removeProtocol($string)
    {
        return preg_replace('/(^\w+:|^)\/\//', "", $string);
    }

    private function removeSlash($string)
    {
        return rtrim($string, '/\\');
    }

    public function generatePreviewLink($imageName, $entityClassName, $width, $height)
    {
        $folder = substr($this->cleanImageName($imageName), 0, 2);

        return $this->router->generate('front.get_preview', [
            'parent_folder' => $entityClassName,
            'folder' => $folder,
            'filename' => "{$width}x{$height}_{$imageName}",
        ]);
    }

    private function cleanImageName($imageName) {
        $imageName =  str_replace("crop_", "", $imageName);
        $imageName =  str_replace("original_", "", $imageName);
        return $imageName;
    }
}