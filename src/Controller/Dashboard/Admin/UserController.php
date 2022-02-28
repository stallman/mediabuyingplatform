<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\User;
use App\Controller\Dashboard\DashboardController;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends DashboardController
{
    /**
     * @Route("/admin/user/list", name="admin_dashboard.user_list")
     */
    public function listAction()
    {
        $users = $this->entityManager->getRepository(User::class)->findBy([], ['createdAt' => 'DESC']);
        $columns = [
            [
                'label' => 'ID',
                'defaultTableOrder' => 'desc',
                'sortable' => true
            ],
            [
                'label' => 'Email',
                'sortable' => true,
            ],
            [
                'label' => 'Группа'
            ],
            [
                'label' => 'Статус',
                'sortable' => true
            ],
            [
                'label' => 'Никнейм',
                'sortable' => true,
            ],
            [
                'label' => 'Telegram',
                'sortable' => true
            ],
            [
                'label' => ''
            ]
        ];

        return $this->render('dashboard/admin/user/list.html.twig', [
            'h1_header_text' => 'Список пользователей',
            'new_button_action_link' => $this->generateUrl('admin_dashboard.user_new', []),
            'new_button_label' => 'Новый пользователь',
            'users' => $users,
            'columns' => $columns,
        ]);
    }

    /**
     * @Route("/admin/user/new", name="admin_dashboard.user_new")
     *
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function newAction(UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $user->setStatus(true);

        $form = $this->createForm(UserType::class, $user, ['is_password_required' => true])->handleRequest($this->request);

        if ($form->isSubmitted()) {
            if ($form->isValid() && $form->getData()->getPlainPassword()) {
                $user = $form->getData();
                $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));

                $this->entityManager->persist($form->getData());
                $this->entityManager->flush();

                $this->addFlash('success', $this->getFlashMessage('user_create'));

                return $this->redirectToRoute('admin_dashboard.user_list');
            } else {
                $this->addFlash('error', $this->getFlashMessage('user_create_error'));
            }
        }

        return $this->render('dashboard/admin/user/form.html.twig', [
            'h1_header_text' => 'Новый пользователь',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/user/edit/{id}", name="admin_dashboard.user_edit")
     *
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return Response
     */
    public function editAction(User $user, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createForm(UserType::class, $user, ['is_password_required' => false])->handleRequest($this->request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $user = $form->getData();
                $newPassword = $user->getPlainPassword();
                if ($newPassword) {
                    $user->setPassword($encoder->encodePassword($user, $newPassword));
                    $this->addFlash('success', $this->getFlashMessage('user_edit_password'));
                }
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('user_edit'));
            } else {
                $this->addFlash('error', $this->getFlashMessage('user_edit_error'));
            }
        }

        return $this->render('dashboard/admin/user/form.html.twig', [
            'form' => $form->createView(),
            'h1_header_text' => 'Править пользователя ' . $user
        ]);
    }

    /**
     * @Route("/admin/user/login/{id}", name="admin_dashboard.user_login")
     *
     * @return RedirectResponse
     */
    public function loginAction(User $user)
    {
        if (!$user->getStatus()) {
            $this->addFlash('error', $this->getFlashMessage('user_login_inactive_error'));
            return $this->redirectToRoute('admin_dashboard.user_list');
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $firstRoleKey = 0;
        switch ($user->getRoles()[$firstRoleKey]) {
            case "ROLE_ADMIN":
                $this->get('session')->set('_security_admin', serialize($token));
                return $this->redirectToRoute('admin_dashboard');
            case "ROLE_MEDIABUYER":
                $this->get('session')->set('_security_mediabuyer', serialize($token));
                return $this->redirectToRoute('mediabuyer_dashboard');
            case "ROLE_JOURNALIST":
                $this->get('session')->set('_security_journalist', serialize($token));
                return $this->redirectToRoute('journalist_dashboard');
        }

        $this->addFlash('error', $this->getFlashMessage('user_login_auth_error'));
        return $this->redirectToRoute('admin_dashboard.user_list');
    }

}