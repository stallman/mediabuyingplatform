<?php


namespace App\Service;


use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExchangeRatesApi
{
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->apiKey = $params->get('currency_api');
        $this->logger = $logger;
    }

    public function getRate(string $base, string $target, string $date): float
    {
        /**
         * http://free.currencyconverterapi.com/api/v5/convert?q=UAH_RUB&compact=y&apiKey=3edbeac996a52218f3d3
         */
        $exchangeResult = 0.0000;
        $rates = mb_strtoupper($base . '_' . $target);

        try {
            // Initialize cURL.
            $ch = curl_init();

            // Set the URL that you want to GET by using the CURLOPT_URL option.
            //curl_setopt($ch, CURLOPT_URL, 'http://free.currencyconverterapi.com/api/v5/convert?q=' . $rates . '&compact=y&apiKey=' . $this->apiKey . '');
            $url = 'https://free.currconv.com/api/v7/convert?q=' . $rates . '&compact=ultra&apiKey=' . $this->apiKey . '&date=' . $date;

            curl_setopt($ch, CURLOPT_URL, $url);

            // Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the request.
            $data = curl_exec($ch);

            // Close the cURL handle.
            curl_close($ch);

            // Print the data out onto the page.
            //echo $data;
        } catch (\Exception $e) {
            $this->logger->error('Currency exchange. Exception. ' . $e->getMessage(), $e->getTrace());
        } finally {
            $data = \json_decode($data, true);
            $context = [
                'data' => $data,
                'rates' => $rates,
                'date' => $date,
            ];

            // response like: {"USD_RUB":{"2021-08-30":73.471501}}
            // response like: {"status":400,"error":"Currency USD is unavailable from 2021-09-05 to 2021-09-05"}
            if (isset($data[$rates]) && isset($data[$rates][$date])) {
                $exchangeResult = $data[$rates][$date];
                $this->logger->info('Currency exchange. Success.', $context);
            } else {
                $this->logger->error('Currency exchange. Bad response.', $context);
            }
        }

        return $exchangeResult;
    }
}
