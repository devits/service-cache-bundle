<?php
namespace Epiphany\ServiceCacheBundle\Proxy;

class MethodData
{

    /**
     * [$reflectionMethod description]
     * @var \ReflectionMethod
     */
    protected $reflectionMethod;
    protected $serviceCacheEnabled;
    protected $keyElements;
    protected $options;
    protected $expires;

    public function __construct() {

        // seconds till data expiry - zero = no-expire
        $this->expires              = 0;
        $this->serviceCacheEnabled  = false;
        $this->keyElements          = array();
        $this->options              = array();
    }

    /**
     * Getter for expires
     *
     * @return mixed
     */
    public function getExpires()
    {
        return $this->expires;
    }
    
    /**
     * Setter for expires
     *
     * @param mixed $expires Value to set
     * @return self
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
        return $this;
    }

    /**
     * Getter for reflectionMethod
     *
     * @return \ReflectionMethod
     */
    public function getReflectionMethod()
    {
        return $this->reflectionMethod;
    }
    
    /**
     * Setter for reflectionMethod
     *
     * @param \ReflectionMethod $reflectionMethod Value to set
     * @return self
     */
    public function setReflectionMethod(\ReflectionMethod $reflectionMethod)
    {
        $this->reflectionMethod = $reflectionMethod;
        return $this;
    }

    /**
     * Getter for serviceCacheEnabled
     *
     * @return mixed
     */
    public function getServiceCacheEnabled()
    {
        return $this->serviceCacheEnabled;
    }
    
    /**
     * Setter for serviceCacheEnabled
     *
     * @param mixed $serviceCacheEnabled Value to set
     * @return self
     */
    public function setServiceCacheEnabled($serviceCacheEnabled)
    {
        $this->serviceCacheEnabled = $serviceCacheEnabled;
        return $this;
    }

    /**
     * Getter for keyElements
     *
     * @return mixed
     */
    public function getKeyElements()
    {
        return $this->keyElements;
    }
    
    /**
     * Setter for keyElements
     *
     * @param mixed $keyElements Value to set
     * @return self
     */
    public function setKeyElements($keyElements)
    {
        $this->keyElements = $keyElements;
        return $this;
    }

    /**
     * Getter for options
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * Setter for options
     *
     * @param mixed $options Value to set
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
}
