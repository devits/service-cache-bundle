Epiphany Service Cache Bundle
=============================

Allows caching of Symfony2 service method calls. This is achieved by annotating the required methods in the service class.

This functionality may be useful where:
1. An application makes expensive calls to an API which could be cached or shared with another application, reducing overall API calls
2. An application makes time consuming calls to a service which could be cached for faster performance 
3. As a means to hook into a method call for a service, then capture its parameters and return value 

Configuration
-------------

This assumes you're working with a Symfony2 (v2.3) application, and using composer for package management

There are four steps required to use this bundle.

1. Add this package to your composer.json file and run a *composer update*
2. Update your application's *app/autoload.php* file to include a call to ProxyGenerator::registerNamespace(), as below, so the application can load proxy classes

```php

use Epiphany\ServiceCacheBundle\Proxy\ProxyGenerator;

....

ProxyGenerator::registerNamespace($loader,__DIR__); 

return $loader;
```

3. Tag any services you want to register for caching, and tag a service which implements the *Epiphany\ServiceCacheBundle\Cache\ServiceCacheInterface*. This should be done in your *service.yml/xml* files

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

4. Annotate any services you want to cache..

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

Notes
-----

This package expects the Symfony2 *logger* service to be available in the container (usually monolog, though any service implementing the *Symfony\Component\HttpKernel\Log\LoggerInterface* can be used).  

Release Notes
=============

v1.0.1 - September 23 2013
--------------------------

- Readme updates.

v1.0.0 - September 23 2013
--------------------------

- Initial Commit, basic functionailty.

