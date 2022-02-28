<?php


namespace App\Traits\Dashboard;

use App\Form as Form;
use App\Entity as Entity;
use Symfony\Component\Form\FormInterface;
use App\Entity\User;

trait UsersTrait
{
    private function getMediabuyerUsers() {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'buyer0@demo.com']);
        if ($user) {
            return [$user];
        }
        return [];
        // $dql = "SELECT u FROM App\Entity\User u WHERE u.roles LIKE :role";
        // return $this->entityManager
        //     ->createQuery($dql)
        //     ->setParameter(
        //         'role', '%ROLE_MEDIABUYER%'
        //     )
        //     ->getResult();
    }
}