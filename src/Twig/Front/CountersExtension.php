<?php


namespace App\Twig\Front;


use App\Twig\AppExtension;
use Symfony\Component\Yaml\Yaml;
use Twig\TwigFunction;

class CountersExtension extends AppExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_counters', [$this, 'getCounters']),
        ];
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getCounters()
    {
        $counters = ['head' => null, 'body' => null];
        $counters = Yaml::parseFile($this->container->getParameter('counters_config'));

        return $counters;
    }
}