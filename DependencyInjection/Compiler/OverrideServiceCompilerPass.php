<?php

namespace Epiphany\ServiceCacheBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Epiphany\ServiceCacheBundle\Proxy\ProxyGenerator;
use Symfony\Component\DependencyInjection\Reference;
use Epiphany\ServiceCacheBundle\Proxy\ServiceProxyException;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        // find our tagged cache service
        $cacheServices = $container->findTaggedServiceIds(
            'epiphany_service_cache.cache'
        );

        $registeredServices = $container->findTaggedServiceIds(
            'epiphany_service_cache.register'
        );

        // if we've registered services with the service cache but not tagged a cache 
        // service then throw an exception
        if(count($registeredServices) > 0 && count($cacheServices) == 0) {

            throw new ServiceProxyException("Services have been tagged for use with the service cache, but " . 
                "no cache service has been tagged! A service implementing ServiceCacheInterface should be tagged " .
                "with 'epiphany_service_cache.cache'");
        }

        foreach ($cacheServices as $serviceId => $attributes) {

            $serviceCacheId = $serviceId;   

            // should only be one cache service registered, possibly add precedent tags later
            break;         
        }

        $proxyGen = new ProxyGenerator($container->getParameter('kernel.root_dir'));

        foreach ($registeredServices as $serviceId => $attributes) {

            $serviceDefinition = $container->getDefinition($serviceId);

            $proxyClass = $proxyGen->generate($serviceDefinition->getClass());

            $serviceDefinition->setClass($proxyClass);

            // inject the data store service into the proxy
            $serviceDefinition->addMethodCall(
                'setServiceCache', 
                array(new Reference($serviceCacheId))
            );

            // inject a logger into the proxy
            $serviceDefinition->addMethodCall(
                'setLogger', 
                array(new Reference('logger'))
            );

        }
    }
}