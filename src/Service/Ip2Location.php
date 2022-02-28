<?php


namespace App\Service;


use App\Traits\UserGeoDataTrait;
use IP2Location\Database;
use IP2Location\WebService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\Cloner\Data;

class Ip2Location
{
    use UserGeoDataTrait;

    /** @var mixed|null */
    public $userIp;
    private array $userGeoData = [];
    private Request $request;
    private ParameterBagInterface $parameters;
    /** @var WebService | Database */
    private $ip2location;
    private string $ip2locationApiKey;
    private string $ip2locationPackage;
    private string $ip2locationUseSsl;
    private string $ip2locationLanguage;
    private array $lookupResult = [];
    private string $lookupMethod;

    /**
     * Ip2Location constructor.
     * @param Request $request
     * @param ParameterBagInterface $parameters
     * @throws \Exception
     */
    public function __construct(Request $request, ParameterBagInterface $parameters)
    {
        $this->request = $request;
        $this->parameters = $parameters;
        //TODO по задаче - IP можно передавать через гет параметр ip. Если он передан то использовать его,
        // если нет то берем реальный ip пользователя (нужно для тестирования).
        //$this->userIp = $this->request->server->get('REMOTE_ADDR');
        $this->userIp = $this->request->get('ip') ? $this->request->get('ip') : $this->request->server->get('REMOTE_ADDR');
        $this->ip2locationApiKey = $this->parameters->get('ip2location_api_key');
        $this->ip2locationPackage = $this->parameters->get('ip2location_package');
        $this->ip2locationUseSsl = $this->parameters->get('ip2location_use_ssl');
        $this->ip2locationLanguage = $this->parameters->get('ip2location_language');
        $this->lookupMethod = $this->parameters->get('ip2location_lookup_method');

        $this->runLookup();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function runLookup()
    {
        switch ($this->lookupMethod) {
            case 'webservice':
                 $this->runWebServiceLookup();
                 break;
            case 'database':
                $this->runDatabaseLookup();
                break;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function initWebServiceLookup()
    {
        $this->ip2location = new \IP2Location\WebService(
            $this->ip2locationApiKey,
            $this->ip2locationPackage,
            $this->ip2locationUseSsl
        );

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function initDatabaseLookup()
    {
        $this->ip2location = new Database($this->parameters->get('ip2location_ip_db'), Database::FILE_IO);

        return $this;
    }


    /**
     * @return $this
     * @throws \Exception
     */
    private function runWebServiceLookup()
    {
        $this->initWebServiceLookup();

        $geoDataAddons = [
            'continent',
            'country',
            'region',
            'city',
            'geotargeting',
            'country_groupings',
            'time_zone_info'
        ];

        $this->lookupResult = $this->ip2location->lookup($this->userIp, $geoDataAddons, $this->ip2locationLanguage);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function runDatabaseLookup()
    {
        $this->initDatabaseLookup();

        $this->lookupResult = $this->ip2location->lookup($this->userIp, Database::ALL, $this->ip2locationLanguage);

        return $this;
    }
}