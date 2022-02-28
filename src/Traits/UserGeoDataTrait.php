<?php


namespace App\Traits;


trait UserGeoDataTrait
{
    /**
     * @return string
     */
    public function getUserCountry()
    {
        $countryName = null;

        switch ($this->lookupMethod) {
            case 'webservice':
                $countryName = isset($this->lookupResult['country']['translations'][$this->ip2locationLanguage]) ? $this->lookupResult['country']['translations'][$this->ip2locationLanguage] : $this->lookupResult['country']['name'];
                break;
            case 'database':
                $countryName = isset($this->lookupResult['countryName']) ? $this->lookupResult['countryName'] : null;
                break;
        }

        return $countryName;
    }

    /**
     * @return |null
     */
    public function getUserRegion()
    {
        $regionName = null;

        switch ($this->lookupMethod) {
            case 'webservice':
                $regionName = isset($this->lookupResult['region']['translations'][$this->ip2locationLanguage]) ? $this->lookupResult['region']['translations'][$this->ip2locationLanguage] : $this->lookupResult['region']['name'];
                break;
            case 'database':
                $regionName = isset($this->lookupResult['regionName']) ? $this->lookupResult['regionName'] : null;
                break;
        }

        return $regionName;
    }

    /**
     * @return |null
     */
    public function getUserCity()
    {
        $cityName = null;

        switch ($this->lookupMethod) {
            case 'webservice':
                $cityName = isset($this->lookupResult['city']['translations'][$this->ip2locationLanguage]) ? $this->lookupResult['city']['translations'][$this->ip2locationLanguage] : $this->lookupResult['city']['name'];
                break;
            case 'database':
                $cityName = isset($this->lookupResult['cityName']) ? $this->lookupResult['cityName'] : null;
                break;
        }

        return $cityName;
    }

    /**
     * @return string|null
     */
    public function getUserCountryCode()
    {
        $countryCode = null;

        switch ($this->lookupMethod) {
            case 'webservice':
                $countryCode = $this->lookupResult['country_code'];
                break;
            case 'database':
                $countryCode = $this->lookupResult['countryCode'];
                break;
        }

        return $countryCode;
    }

    /**
     * @return array
     */
    public function getAllData()
    {
        return $this->lookupResult;
    }
}