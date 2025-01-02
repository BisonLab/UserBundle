<?php

namespace BisonLab\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use BisonLab\UserBundle\Lib\ExternalEntityConfig;

class BisonLabUserBundle extends Bundle
{
    public function boot(): void
    {
       ExternalEntityConfig::setRolesConfig($this->container->getParameter('bisonlab_user.roles'));
    }
}
