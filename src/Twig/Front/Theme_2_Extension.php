<?php


namespace App\Twig\Front;


use App\Entity\NewsCategory;
use App\Twig\AppExtension;
use Twig\TwigFunction;

class Theme_2_Extension extends AppExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('render_theme_2_nav_bar', [$this, 'renderNavBar']),
            new TwigFunction('render_theme_2_burger_menu', [$this, 'renderBurgerMenu']),
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

        return $this->twigEnvironment->render('front/theme_2/partials/nav_bar.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderBurgerMenu()
    {
        $categories = $this->entityManager->getRepository(NewsCategory::class)->findBy([
            'isEnabled' => true,
        ]);

        return $this->twigEnvironment->render('front/theme_2/partials/burger_menu.html.twig', [
            'categories' => $categories,
        ]);
    }
}