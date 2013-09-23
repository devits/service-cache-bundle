<?php
namespace Epiphany\ServiceCacheBundle\Proxy;

class ProxyHelper
{

    /**
     * build a key from params and the key elements defined
     * 
     * @param  array  $params      [description]
     * @param  array  $keyElements [description]
     * @param  [type] $now         [description]
     * @return [type]              [description]
     */
    public static function generateKey(array $params, array $keyElements, \DateTime $now = null) {

        if(!$now)
            $now = new \DateTime('now');

        $key = '';

        // parameter key elements first
        foreach ($keyElements as $keyEl) {

            if($key !== '')
                $key .= '_';

            if($keyEl->getType() === KeyElement::TypeParameter) {

                $idx = $keyEl->getParamIdx();

                if(!isset($params[$idx])) {

                    // use the optional value instead
                    $pVal = $keyEl->getOptionalArgDefaultValue();
                }
                else { 

                    $pVal = $params[$idx];
                }     

                // here, we're attempting to stringify any key parameters to produce
                // the key string - object params will need to implement __toString() 
                // magic method, array parameters are imploded           
                if(is_array($pVal))
                    $key .= implode('_', $pVal);
                else {

                    if(is_object($pVal) && get_class($pVal) == 'DateTime')
                        $key = $pVal->format('Y-m-d-H-i-s');
                    else // string cast all other types of parameter
                        $key .= (string) $pVal; 
                }
            }
            else if($keyEl->getType() === KeyElement::TypeDate) {

                $dateFormat = $keyEl->getVal();

                $key .= $now->format($dateFormat);
            }
        }

        return $key;
    }


    /**
     * build meta data to accompany doc in the data store 
     * this will be used for query 
     * 
     * @param  array  $params      [description]
     * @param  array  $keyElements [description]
     * @param  [type] $now         [description]
     * @return [type]              [description]
     */
    public static function generateMeta(array $params, array $keyElements, \DateTime $now = null) {

        if(!$now)
            $now = new \DateTime('now');

        $meta = array();

        foreach ($keyElements as $keyEl) {

            if($keyEl->getType() === KeyElement::TypeParameter) {

                $idx       = $keyEl->getParamIdx();
                $paramName = $keyEl->getVal();

                if(!isset($params[$idx])) {

                    // use the optional value instead
                    $pVal = $keyEl->getOptionalArgDefaultValue();

                }
                else { 

                    // parameter was passed in
                    $pVal = $params[$idx];
                }

                $meta[$keyEl->getVal()] = $pVal;;
            }
            elseif($keyEl->getType() === KeyElement::TypeDate) {

                $dateFormat = $keyEl->getVal();                

                $meta['date'] = $now->format($dateFormat);
            }
        }

        return $meta;
    }
}