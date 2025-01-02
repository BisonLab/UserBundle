<?php

namespace BisonLab\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use BisonLab\UserBundle\Lib\ExternalEntityConfig;
use BisonLab\UserBundle\Entity\Group;

#[ORM\Table(name: 'bisonlab_user')]
#[ORM\Entity(repositoryClass: 'BisonLab\UserBundle\Repository\UserRepository')]
#[UniqueEntity('username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use UserTrait;

    #[ORM\JoinTable(name: 'bisonlab_users_groups')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'BisonLab\UserBundle\Entity\Group', inversedBy: 'users')]
    private $groups;
}
