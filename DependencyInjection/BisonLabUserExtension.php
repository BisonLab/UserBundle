<?php

namespace BisonLab\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BisonLabUserExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // ... you'll load the files here later
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../config')
        );
        $loader->load('services.yaml');
    }
}
