<?php

namespace BisonLab\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use BisonLab\UserBundle\Lib\ExternalEntityConfig;
use BisonLab\UserBundle\Entity\Group;

/**
 * @ORM\Entity(repositoryClass="BisonLab\UserBundle\Repository\UserRepository")
 * @ORM\Table(name="bisonlab_user")
 * @UniqueEntity("username")
 */
class User implements UserInterface
{
    use UserTrait;
}
