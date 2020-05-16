<?php

namespace BisonLab\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use BisonLab\UserBundle\Lib\ExternalEntityConfig;

/**
 * @ORM\Entity(repositoryClass="BisonLab\UserBundle\Repository\GroupRepository")
 * @ORM\Table(name="bisonlab_group")
 */
class Group
{
    use GroupTrait;
}
