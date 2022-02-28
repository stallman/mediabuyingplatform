<?php


namespace App\Traits\Dashboard;


use App\Entity\Country;
use App\Form\CountryType;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Traits\Dashboard\FlashMessagesTrait;

trait CountryActionTrait
{
    use FlashMessagesTrait;

    public function countryList()
    {
        $countries = $this->entityManager->getRepository(Country::class)->findAll();
        $columns = [
            [
                'label' => 'Название',
            ],
            [
                'label' => 'Код',
            ],
            [
                'label' => ''
            ]
        ];

        return $this->render('dashboard/admin/settings/country/list.html.twig', [
            'h1_header_text' => 'Страны',
            'new_button_action_link' => $this->generateUrl('admin_dashboard.settings.country_add'),
            'new_button_label' => 'Добавить страну',
            'columns' => $columns,
            'countries' => $countries,
        ]);
    }

    public function countryAdd()
    {
        $country = new Country();
        $form = $this->createForm(CountryType::class, $country)->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_dashboard.settings.country_list');
        }

        return $this->render('dashboard/admin/settings/country/form.html.twig', [
            'form' => $form->createView(),
            'h1_header_text' => 'Добавление страны'
        ]);
    }

    public function countryEdit(Country $country)
    {
        $form = $this->createForm(CountryType::class, $country)->handleRequest($this->request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('country_edit'));
            } else {
                $this->addFlash('error',  $this->getFlashMessage('country_edit_error'));
            }

            return $this->redirectToRoute('admin_dashboard.settings.country_list');
        }

        return $this->render('dashboard/admin/settings/country/form.html.twig', [
            'form' => $form->createView(),
            'h1_header_text' => 'Редактирование страны'
        ]);
    }

    public function countryDelete(Country $country)
    {
        //fixme добавить в условие когда будет готова связь с тизерами
        // || $country->getTeasers()->getValues() && !empty($country->getTeasers()->getValues())
        if ($country->getNews()->getValues() && !empty($country->getNews()->getValues())) {
            $this->addFlash('error', $this->getFlashMessage('country_delete_error'));

            return JsonResponse::create('', 200);
        }

        try {
            $this->entityManager->remove($country);
            $this->entityManager->flush();
            $this->addFlash('success', $this->getFlashMessage('country_delete'));

            return JsonResponse::create('', 200);

        } catch (\Exception $exception) {

            return JsonResponse::create('Ошибка при удалении страны', 500);
        }

    }
}