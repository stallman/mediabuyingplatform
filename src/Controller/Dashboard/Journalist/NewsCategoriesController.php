<?php

namespace App\Controller\Dashboard\Journalist;

use App\Entity\NewsCategory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{RedirectResponse, Response};

class NewsCategoriesController extends JournalistController
{
    /**
     * @Route("/journalist/news-categories/list", name="journalist_dashboard.news_categories_list")
     */
    public function listAction()
    {
        $news_categories = $this->entityManager->getRepository(NewsCategory::class)->getCategoriesWithCountNews();
        $columns = [
            [
                'label' => 'ID',
                'sortable' => true
            ],
            [
                'label' => 'Название',
                'sortable' => true,
                'defaultTableOrder' => 'asc',
            ],
            [
                'label' => 'Слаг',
                'sortable' => true
            ],
            [
                'label' => 'Кол-во новостей в группе',
                'sortable' => true
            ],
        ];

        return $this->render('dashboard/journalist/news-categories/list.html.twig', [
            'news_categories' => $news_categories,
            'columns' => $columns,
            'h1_header_text' => 'Категории новостей',
        ]);
    }
}