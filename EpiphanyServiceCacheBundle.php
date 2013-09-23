<?php

namespace Epiphany\ServiceCacheBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Epiphany\ServiceCacheBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class EpiphanyServiceCacheBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // this pass will override standard service classes with our generated proxy classes
        $container->addCompilerPass(new OverrideServiceCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
