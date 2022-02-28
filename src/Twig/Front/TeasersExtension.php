<?php


namespace App\Twig\Front;


use App\Entity\Teaser;
use App\Twig\AppExtension;
use Symfony\Component\Yaml\Yaml;
use Twig\TwigFunction;

class TeasersExtension extends AppExtension
{

    const MACROS_CITY = '[CITY]';

    public function getFunctions()
    {
        return [
            new TwigFunction('get_teasers', [$this, 'getTeasers']),
            new TwigFunction('get_city', [$this, 'getCity']),
        ];
    }

    /**
     *
     * @param Teaser $teaser
     * @param string $theme
     * @param string $width_teaser_block
     * @param string $height_teaser_block
     * @param object $news
     * @param string $city
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getTeasers($teaser, $theme, $width_teaser_block, $height_teaser_block, $news, $city)
    {
        return $this->twigEnvironment->render("front/$theme/partials/teaser_block.html.twig",
            ['teaser' => $teaser,
                'width_teaser_block' => $width_teaser_block,
                'height_teaser_block' => $height_teaser_block,
                'article' => $news,
                'city' => $city]);
    }
    public function getCity($teaser, $city)
    {
        return str_replace(self::MACROS_CITY, $city, $teaser);
    }
}