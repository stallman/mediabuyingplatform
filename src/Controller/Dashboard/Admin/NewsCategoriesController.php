<?php

namespace App\Controller\Dashboard\Admin;

use App\Controller\Dashboard\DashboardController;
use App\Entity\NewsCategory;
use App\Form\NewsCategoriesType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{RedirectResponse, Response};

class NewsCategoriesController extends DashboardController
{
    /**
     * @Route("/admin/news-categories/list", name="admin_dashboard.news_categories_list")
     */
    public function listAction()
    {
        $newsCategories = $this->entityManager->getRepository(NewsCategory::class)->getCategoriesWithCountNews();
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
            [
                'label' => ''
            ]
        ];

        return $this->render('dashboard/admin/news-categories/list.html.twig', [
            'news_categories' => $newsCategories,
            'columns' => $columns,
            'h1_header_text' => 'Категории новостей',
            'new_button_label' => 'Добавить категорию',
            'new_button_action_link' => $this->generateUrl('admin_dashboard.news_categories_add'),
        ]);
    }

    /**
     * @Route("/admin/news-categories/add", name="admin_dashboard.news_categories_add")
     *
     * @return RedirectResponse|Response
     */
    public function addAction()
    {
        $newsCategory = new NewsCategory();
        $form = $this->createForm(NewsCategoriesType::class, $newsCategory)->handleRequest($this->request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->persist($form->getData());
                $this->entityManager->flush();

                $this->addFlash('success', $this->getFlashMessage('news_category_add'));

                return $this->redirectToRoute('admin_dashboard.news_categories_list');

            } else {
                $this->addFlash('error', $this->getFlashMessage('news_category_add_error'));
            }
        }

        return $this->render('dashboard/admin/news-categories/form.html.twig', [
            'form' => $form->createView(),
            'h1_header_text' => 'Новая категория'
        ]);
    }

    /**
     * @Route("/admin/news-categories/edit/{id}", name="admin_dashboard.news_categories_edit")
     */
    public function editAction(NewsCategory $newsCategory)
    {

        $form = $this->createForm(NewsCategoriesType::class, $newsCategory)->handleRequest($this->request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->flush();

                $this->addFlash('success', $this->getFlashMessage('news_category_edit'));

                return $this->redirectToRoute('admin_dashboard.news_categories_list', []);

            } else {
                $this->addFlash('error', $this->getFlashMessage('news_category_edit_error'));
            }
        }

        return $this->render('dashboard/admin/news/form.html.twig', [
            'h1_header_text' => 'Редактировать категорию',
            'form' => $form->createView(),
        ]);
    }
}