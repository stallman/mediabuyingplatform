<?php


namespace App\Controller\Dashboard\Admin;


use App\Controller\Dashboard\DashboardController;
use App\Entity\Country;
use App\Entity\CropVariant;
use App\Entity\CropVariantCollection;
use App\Form\BasicSettingsType;
use App\Form\CountersType;
use App\Form\CropVariantCollectionType;
use App\Traits\Dashboard\CountryActionTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Yaml\Yaml;


class SettingsController extends DashboardController
{
    use CountryActionTrait;

    /**
     * @Route("/admin/settings/basic-settings/list", name="admin_dashboard.settings.basic_settings_list")
     *
     * @return Response
     */
    public function basicSettingsListAction()
    {
        $basicSettings = Yaml::parseFile($this->getParameter('basic_settings_config'));
        $form = $this->createForm(BasicSettingsType::class, $basicSettings['parameters'])->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $yaml = Yaml::dump(['parameters'=>$form->getData()]);
            file_put_contents($this->getParameter('basic_settings_config'), $yaml);
            $this->addFlash('success', $this->getFlashMessage('basic_settings_edit'));
        }

        return $this->render('dashboard/admin/settings/basic_settings/list.html.twig', [
            'h1_header_text' => 'Основные настройки',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/settings/counters/list", name="admin_dashboard.settings.counters_list")
     *
     * @return RedirectResponse|Response
     */
    public function countersListAction()
    {
        $counters = Yaml::parseFile($this->getParameter('counters_config'));
        $form = $this->createForm(CountersType::class, $counters)->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $yaml = Yaml::dump($form->getData());
            file_put_contents($this->getParameter('counters_config'), $yaml);
        }

        return $this->render('dashboard/admin/settings/counters/list.html.twig', [
            'h1_header_text' => 'Счетчики',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/settings/crop-variant/list", name="admin_dashboard.settings.crop_variant_list")
     *
     * @return Response
     */
    public function cropVariantListAction()
    {
        $form = $this->getCropVariantForm();
        $columns = [
            [
                'label' => 'Порядковый номер дизайна',
            ],
            [
                'label' => 'Ширина изображений для тизерных блоков',
            ],
            [
                'label' => 'Высота изображений для тизерных блоков',
            ],
            [
                'label' => 'Ширина изображений для новостных блоков',
            ],
            [
                'label' => 'Высота изображения для новостных блоков',
            ],
        ];

        return $this->render('dashboard/admin/settings/crop_variant/list.html.twig', [
            'h1_header_text' => 'Варианты кропа',
            'columns' => $columns,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/settings/crop-variant/update/", name="admin_dashboard.settings.crop_variant_update")
     *
     * @return RedirectResponse
     */
    public function cropVariantUpdateAction()
    {
        $form = $this->getCropVariantForm();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('admin_dashboard.settings.crop_variant_list');
    }

    private function getCropVariantForm()
    {
        $cropVariants = $this->entityManager->getRepository(CropVariant::class)->findAll();
        $cropVariantsCollection = new CropVariantCollection();

        foreach ($cropVariants as $cropVariant) {
            $cropVariantsCollection->getCropVariants()->add($cropVariant);
        }

        return $this->createForm(CropVariantCollectionType::class, $cropVariantsCollection, ['action' => $this->generateUrl('admin_dashboard.settings.crop_variant_update')])->handleRequest($this->request);
    }

    /**
     * @Route("/admin/settings/country/list", name="admin_dashboard.settings.country_list")
     *
     * @return Response
     */
    public function countryListAction()
    {
        return $this->countryList();
    }

    /**
     * @Route("/admin/settings/country/add", name="admin_dashboard.settings.country_add")
     *
     * @return RedirectResponse|Response
     */
    public function countryAddAction()
    {
        return $this->countryAdd();
    }

    /**
     * @Route("/admin/settings/country/edit/{id}", name="admin_dashboard.settings.country_edit")
     *
     * @param Country $country
     * @return RedirectResponse|Response
     */
    public function countryEditAction(Country $country)
    {
        return $this->countryEdit($country);
    }

    /**
     * @Route("/admin/settings/country/delete/{id}", name="admin_dashboard.settings.country_delete")
     *
     * @param Country $country
     * @return JsonResponse
     */
    public function countryDeleteAction(Country $country)
    {
        return $this->countryDelete($country);
    }

}