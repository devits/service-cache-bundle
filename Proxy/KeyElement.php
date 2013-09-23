<?php
namespace Epiphany\ServiceCacheBundle\Proxy;

class KeyElement
{
    const TypeParameter     = 'param';
    const TypeDate          = 'date';

    protected $type;
    protected $val;
    protected $paramIdx;
    protected $optionalArgDefaultValue;

    public function __construct($type, $val, $paramIdx = null, $optionalArgDefaultValue = null) {

        if(!in_array($type, array(self::TypeParameter,self::TypeDate)))
            throw new \ServiceProxyException("Attempted to create key element with unknown type. " .
                "Check your annotations!");

        $this->type                     = $type;
        $this->val                      = $val;
        $this->paramIdx                 = $paramIdx;
        $this->optionalArgDefaultValue  = $optionalArgDefaultValue;

    }

    /**
     * Getter for optionalArgDefaultValue
     *
     * @return mixed
     */
    public function getOptionalArgDefaultValue()
    {
        return $this->optionalArgDefaultValue;
    }
    
    /**
     * Setter for optionalArgDefaultValue
     *
     * @param mixed $optionalArgDefaultValue Value to set
     * @return self
     */
    public function setOptionalArgDefaultValue($optionalArgDefaultValue)
    {
        $this->optionalArgDefaultValue = $optionalArgDefaultValue;
        return $this;
    }
    
    /**
     * Getter for paramIdx
     *
     * @return mixed
     */
    public function getParamIdx()
    {
        return $this->paramIdx;
    }
    
    /**
     * Setter for paramIdx
     *
     * @param mixed $paramIdx Value to set
     * @return self
     */
    public function setParamIdx($paramIdx)
    {
        $this->paramIdx = $paramIdx;
        return $this;
    }

    /**
     * Getter for type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Setter for type
     *
     * @param mixed $type Value to set
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Getter for val
     *
     * @return mixed
     */
    public function getVal()
    {
        return $this->val;
    }
    
    /**
     * Setter for val
     *
     * @param mixed $val Value to set
     * @return self
     */
    public function setVal($val)
    {
        $this->val = $val;
        return $this;
    }
    
    
}
