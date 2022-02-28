<?php

namespace App\QueryBuilder;

use Doctrine\ORM\QueryBuilder;

class UserQueryBuilder extends QueryBuilder
{
    public function hasRole(string $role) : self {
        return $this
            ->andWhere("u.roles LIKE :role")
            ->setParameter('role', '%' . $role . '%');
    }
}
