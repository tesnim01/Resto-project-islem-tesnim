<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'admin')]
class Admin extends User
{
    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }
}
