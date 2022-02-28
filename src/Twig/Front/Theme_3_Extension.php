<?php


namespace App\Twig\Front;


use App\Entity\NewsCategory;
use App\Twig\AppExtension;
use Twig\TwigFunction;

class Theme_3_Extension extends AppExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('render_theme_3_nav_bar', [$this, 'renderNavBar']),
            new TwigFunction('render_send_pulse_script', [$this, 'renderSendPulseScript']),
            new TwigFunction('generate_preview_link', [$this, 'generatePreviewLink']),
        ];
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderNavBar()
    {
        $categories = $this->entityManager->getRepository(NewsCategory::class)->getEnabledCategories();

        return $this->twigEnvironment->render('front/theme_3/partials/nav_bar.html.twig', [
            'categories' => $categories,
        ]);
    }
}