<?php
namespace Epiphany\ServiceCacheBundle\Cache;

interface ServiceCacheInterface {
    
    /**
     * setDataForKey
     * 
     * @param string  $key
     * @param array   $data    associative array
     * @param integer $expires data expires after n seconds (never expires if zero) 
     * @param array   $options additional name/value pairs 
     */
    public function setDataForKey($key, $data, $expires = 0, array $options = null);

    /**
     * getDataForKey
     * 
     * @param array  $data    associative array
     * @param array  $options additional name/value pairs 
     */
    public function getDataForKey($key, array $options = null);

    /**
     * getCachingEnabled
     *
     * If enabled, data will be retrieved from the cache, when available, upon making a service
     * call. 
     * 
     * If disabled, data will still be stored in the cache via the setDataForKey() method but will never be 
     * retrieved from the cache during a getDataForKey() operation  
     * 
     * @return boolean  (is this service enabled)
     */
    public function getCachingEnabled();
}