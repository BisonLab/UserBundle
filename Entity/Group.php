<?php

namespace BisonLab\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use BisonLab\UserBundle\Lib\ExternalEntityConfig;

#[ORM\Table(name: 'bisonlab_group')]
#[ORM\Entity(repositoryClass: 'BisonLab\UserBundle\Repository\GroupRepository')]
class Group
{
    use GroupTrait;

    #[ORM\ManyToMany(targetEntity: 'BisonLab\UserBundle\Entity\User', mappedBy: 'groups')]
    private $users;
}
