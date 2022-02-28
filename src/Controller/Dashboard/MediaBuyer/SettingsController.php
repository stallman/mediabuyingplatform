<?php


namespace App\Controller\Dashboard\MediaBuyer;


use App\Controller\Dashboard\DashboardController;
use App\Controller\Dashboard\NewsControllerInterface;
use App\Entity\News;
use App\Entity\NewsType;
use App\Entity\User;
use App\Entity\UserSettings;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SettingsController extends DashboardController
{
    /**
     * @Route("/mediabuyer/settings/edit", name="mediabuyer_dashboard.settings_edit")
     */
    public function editAction(UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createSettingsForm($this->getUser());

        $newPassword = $form->get('changed_password')->getData();
        if (!is_null($newPassword) && !empty($newPassword) && $newPassword !== "") {
            $this->getUser()->setPassword($encoder->encodePassword($this->getUser(), $newPassword));
            $this->addFlash('success', $this->getFlashMessage('settings_edit_password'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            foreach (UserSettings::VALID_SLUGS as $slug){
                $this->createOrChangeUserSetting($form, $slug);
            }
            $this->addFlash('success', $this->getFlashMessage('settings_edit'));
            return $this->redirectToRoute('mediabuyer_dashboard.settings_edit', []);
        }
        foreach (UserSettings::VALID_SLUGS as $slug){
            $this->getUserSettings($form, $slug);
        }

        return $this->render('dashboard/mediabuyer/settings/form.html.twig', [
            'h1_header_text' => 'Настройки',
            'form' => $form->createView(),
        ]);
    }

    private function getUserSettings($form, $slug) {
        $repo = $this->entityManager->getRepository(UserSettings::class)->findBy(
            ['slug' => $slug, 'user' => $this->getUser()]
        ) ;

        if (!empty($repo)) {
            return $form->get($slug)->setData($repo[0]->getValue());
        }else{
            if($slug == UserSettings::SLUG_STATS_STORAGE_DAYS) {
                return $form->get($slug)->setData(UserSettings::DEFAULT_STATS_STORAGE_DAYS);
            }
        }

    }

    private function createOrChangeUserSetting($form, $slug) {
        $ecrmViewCount = intval($form->get($slug)->getData());

        $userSettingsRepo = $this->entityManager->getRepository(UserSettings::class)->findBy(
            ['slug' => $slug, 'user' => $this->getUser()]
        );

        if(empty($userSettingsRepo)) {
            $userSettings = new UserSettings();
            $userSettings->setUser($this->getUser());
            $userSettings->setSlug($slug);
            $userSettings->setValue($ecrmViewCount);
            $this->entityManager->persist($userSettings);
        } else {
            $userSettingsRepo[0]->setValue($ecrmViewCount);
        }
        $this->entityManager->flush();
    }

}
