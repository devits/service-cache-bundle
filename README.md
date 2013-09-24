Epiphany Service Cache Bundle
=============================

Allows caching of Symfony2 service method calls. This is achieved by annotating the required methods in the service class.

This functionality may be useful where:

1. An application makes expensive calls to an API which could be cached or shared with another application, reducing overall API calls.
2. An application makes time consuming calls to a service which could be cached for faster performance. 
3. As a means to hook into a method call for a service, then capture its parameters and return value. 

Configuration
-------------

This assumes you're working with a Symfony2 (v2.3) application, and using composer for package management.

There are four steps required to use this bundle.

* Add this package to your composer.json file and run a *composer update*
* Update your application's *app/autoload.php* file to include a call to ProxyGenerator::registerNamespace(), as below, so the application can load proxy classes

```php

use Epiphany\ServiceCacheBundle\Proxy\ProxyGenerator;

....

ProxyGenerator::registerNamespace($loader,__DIR__); 

return $loader;
```

* Tag any services you want to register for caching, and tag a service which implements the *Epiphany\ServiceCacheBundle\Cache\ServiceCacheInterface*. This should be done in your *service.yml/xml* files

```yml
services:
    # note the 'epiphany_service_cache.register' tag - this indicates our weather data
    # service should have some (or all) of its method calls cached 
    weather_data_service:
        class: Epiphany\ServiceCacheDemoBundle\Service\WeatherDataService
        arguments: []
        tags:
            - { name: epiphany_service_cache.register}

    # note the 'epiphany_service_cache.cache' tag - this indicates the service
    # should be used by the service_cache as the caching mechanism 
    simple_cache_service:
        class: Epiphany\ServiceCacheDemoBundle\Service\SimpleCacheService
        arguments: [localhost, 12345]
        tags:
            - { name: epiphany_service_cache.cache}
```

* Annotate any services you want to cache..

```php
class WeatherDataService
{
    /**
     * Get the weather forecast for a date and location
     *
     * @service-cache-enable
     *
     * @service-cache-key param date
     * @service-cache-key param location
     * @service-cache-key date Y-m-d-H
     *
     * @service-cache-option compressed y 
     * 
     * @service-cache-expires 0
     * 
     * @param  DateTime $date     
     * @param  string   $location 
     * @return array    forecast data
     */
    public function forecastForDate(\DateTime $date, $location) {

        //....
```

Annotations
-----------

`@service-cache-enable` - This marks a method for use with the Service Cache

`@service-cache-key <date|param> <value>` - One or more of these annotations must be used to define a unique key to store any data against. Any method parameter which can be cast as a string, or the current date (with a format string) can be used.

`@service-cache-option <name> <value>` - Name/value pairs that will be passed to the caching service during get/set operations via an associative array. See the *Epiphany\ServiceCacheBundle\Cache\ServiceCacheInterface*. This allows a the user to pass extra information that might be specific to their implementation of the cache service such as compression, data expiry, (collection name if you're using MongoDB)

`@service-cache-expires <n>` - Expiry in seconds of the cached data. Zero for never expires.


Implementing the Cache Mechanism
--------------------------------

It is at the user's discretion as to what means of caching should be used. All that is required is that the user provides a Symfony2 service marked with the *'epiphany_service_cache.cache'* tag, which implements the *Epiphany\ServiceCacheBundle\Cache\ServiceCacheInterface*. The getDataForKey() method should return a null value when no data can be retrieved from the cache.

Notes
-----

This package expects the Symfony2 *logger* service to be available in the container (usually monolog, though any service implementing the *Symfony\Component\HttpKernel\Log\LoggerInterface* can be used).  

To insert the caching layer, this package produces proxy objects for any marked service, and then overrides the service definition in Symfony's pre-optimization compiler pass of the service container. Currently, the proxy objects must be manually deleted from the application's *app/cache/dsproxy* directory whenever their service is updated.

```php
class EpiphanyServiceCacheBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // this pass will override standard service classes with our generated proxy classes
        $container->addCompilerPass(new OverrideServiceCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
``` 

Release Notes
=============

v1.0.2 - September 23 2013
--------------------------

- Readme updates.

v1.0.1 - September 23 2013
--------------------------

- Readme updates.

v1.0.0 - September 23 2013
--------------------------

- Initial Commit, basic functionality.

