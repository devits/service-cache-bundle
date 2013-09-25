<?php
namespace Epiphany\ServiceCacheBundle\Proxy;

class ProxyGenerator
{
    const ProxyNamespace    = 'DsProxy';
    const ProxyDir          = '/cache/dsproxy';

    protected $proxyDir;
    protected $proxyNamespace;
    protected $fileSystem;
    protected $fileBuilder; 

    public function __construct($kernelRootDir) {

        $this->proxyDir         = $kernelRootDir . self::ProxyDir;
        $this->proxyNamespace   = self::ProxyNamespace;
        $this->fileSystem       = new FileSystem();
        $this->fileBuilder      = $this;
    }

    /**
     * [registerNamespace description]
     * @param  [type] $loader        [description]
     * @param  [type] $kernelRootDir [description]
     * @return [type]                [description]
     */
    public static function registerNamespace($loader, $kernelRootDir) {

        $loader->set(self::ProxyNamespace, $kernelRootDir . self::ProxyDir);         
    }

    /**
     * Setter for fileBuilder
     *
     * @param mixed $fileBuilder Value to set
     * @return self
     */
    public function setFileBuilder($fileBuilder)
    {
        $this->fileBuilder = $fileBuilder;
        return $this;
    }

    /**
     * Setter for fileSystem
     *
     * @param mixed $fileSystem Value to set
     * @return self
     */
    public function setFileSystem($fileSystem)
    {
        $this->fileSystem = $fileSystem;
        return $this;
    }

    /**
     * Generate a proxy class for a target class 
     *
     * Return the generated proxy class name
     * 
     * @param  [type] $targetClassName [description]
     * @return [type]                  [description]
     */
    public function generate($targetClassName)
    {
        $proxyClassName = $this->getProxyClassName($targetClassName);
        $proxyFilename  = $this->getProxyFilename($proxyClassName);

        // proxy class may already exist 
        if(!class_exists($proxyClassName)) {

            if(!$this->fileSystem->fileExists($proxyFilename))
                $this->fileBuilder->buildProxyClassfile($proxyClassName, $targetClassName, $proxyFilename);

           require $proxyFilename;
        }

        return $proxyClassName;
    }

    /**
     * create a new class file which extends the original
     *
     * override any methods with the appropriate annotations
     * 
     * @param  [type] $proxyClassName  [description]
     * @param  [type] $targetClassName [description]
     * @param  [type] $proxyFilename   [description]
     * @return [type]                  [description]
     */
    public function buildProxyClassfile($proxyClassName, $targetClassName, $proxyFilename) {

        $r = new \ReflectionClass($targetClassName);

        $methodData = $this->getMethodData($r);

        $fileContents = $this->createClassFileContents($proxyClassName, $targetClassName, $methodData);

        $this->fileSystem->filePutContents($proxyFilename, $fileContents);
    }

    public function createClassFileContents($proxyClassName, $targetClassName, array $methodData) {

        preg_match('/^(.*)\\\([a-zA-Z_]*)$/', $proxyClassName, $matches );

        if(count($matches) != 3)
            throw new ServiceProxyException('Unexpected proxy class name: ' . $proxyClassName);

        $pName = $matches[2];
        $pNamespace = $matches[1];

        preg_match('/^(.*)\\\([a-zA-Z]*)$/', $targetClassName, $matches );

        if(count($matches) != 3)
            throw new ServiceProxyException('Unexpected target class name: ' . $targetClassName);

        $tName = $matches[2];
        $tNamespace = $matches[1];

        $contents =  "<?php\n";
        
        $contents .= "namespace $pNamespace;\n"; 

        $contents .= "use $targetClassName;\n";         

        $contents .= "use Epiphany\ServiceCacheBundle\Proxy\ProxyHelper;\n";  

        $contents .= "use Epiphany\ServiceCacheBundle\Proxy\KeyElement;\n";  

        $contents .= "class $pName extends $tName {\n";

        // object implementing Epiphany\ServiceCacheBundle\Cache\ServiceCacheInterface
        $contents .= "protected \$serviceCache;\n";

        // object implementing Psr\Log\LoggerInterface (i.e. monolog)
        $contents .= "protected \$logger;\n";

        $contents .= 'public function setServiceCache($sc) { $this->serviceCache = $sc; }' . "\n";

        $contents .= 'public function setLogger($l) { $this->logger = $l; }' . "\n";        

        $contents .= $this->createClassFileMethodOverrides($methodData) . "\n";

        $contents .= "}";

        return $contents;
    }

    public function createClassFileMethodOverrides(array $methodData) {

        $methods = '';

        foreach ($methodData as $md) {

            // don't override this then
            if(!$md->getServiceCacheEnabled()) 
                continue;

            $methods .= $this->createClassFileMethodOverride($md);
        }

        return $methods;
    }


    /**
     * optionsToString
     *
     * output the name/value pairs as an array of options
     * 
     * @param  array  $options
     * @return string 
     */
    public function optionsToString(array $options) {

        return var_export($options, true);
    }

    public function createClassFileMethodOverride(MethodData $md) {

        $methodName         = $md->getReflectionMethod()->getName();
        $methodParams       = $md->getReflectionMethod()->getParameters();
        $optionsArrayString = $this->optionsToString($md->getOptions());
        $expires            = ($md->getExpires() ? $md->getExpires() : 0);

        $methodParamDeclaration = '';
        $methodCallParams       = '';

        $firstParam = true;

        // map param name to idx
        $paramIndexMap = array();

        // map param name to param
        $paramMap = array();

        $paramIdx = 0;

        foreach ($methodParams as $param) {

            $paramIndexMap[$param->getName()] = $paramIdx++;
            $paramMap[$param->getName()]      = $param;

            $methodParamDeclaration .= ($firstParam ? '' : ', ' ) . '$' . $param->getName();
            $methodCallParams .= ($firstParam ? '' : ', ' ) . '$' . $param->getName();

            // param has default value? 
            if($param->isOptional()) {

                $methodParamDeclaration .= 
                    ' = ' . $this->paramToDefaultValueString($param);
            }

            // do we need a class prefix?
            if($param->getClass()) {

                $methodParamDeclaration = '\\' . $param->getClass()->getName() . ' ' . $methodParamDeclaration;
            }

            $firstParam = false;
        }        

        $keyElements = '';
        $first = true;

        foreach ($md->getKeyElements() as $keyElement) {
            
            $type       = $keyElement->getType();
            $val        = $keyElement->getVal();

            if($keyElement->getType() == KeyElement::TypeParameter )
                $paramIdx   = $paramIndexMap[$keyElement->getVal()]; 

            $keyElements .= ($first ? '' : ', ');

            if($keyElement->getType() == KeyElement::TypeParameter ) {

                $optionalVal = $this->paramToDefaultValueString($paramMap[$val]);

                $keyElements .= "new KeyElement('$type', '$val', $paramIdx, $optionalVal) ";
            }
            else
                $keyElements .= "new KeyElement('$type', '$val') ";


            $first = false;
        }

        $method = "
            public function $methodName( $methodParamDeclaration ) {

                \$expires       = '$expires';
                \$key           = ProxyHelper::generateKey(func_get_args(), array($keyElements) );
                \$meta          = ProxyHelper::generateMeta(func_get_args(), array($keyElements) );

                if(\$this->serviceCache->getCachingEnabled()) {

                    \$this->logger->info('Checking cache for data with key: [' . \$key . ']');

                    try {
                        \$data = \$this->serviceCache->getDataForKey(\$key, $optionsArrayString);
                    }
                    catch(\\Exception \$e) {

                        \$this->logger->info('The data store threw an exception : ' . \$e->getMessage());
                        \$data = null;
                    }

                    if(!\$data) {
                        \$this->logger->info('No data found');
                    }
                    else {
                        \$this->logger->info('Data found');

                        return \$data;                          
                    }
                }
                else {
                    \$data = null;
                }

                if(!\$data) {

                    \$this->logger->info('Fetching data from source');    

                    \$data  = parent::$methodName( $methodCallParams );

                    \$this->logger->info('Storing data for key: [' . \$key . ']');
                    
                    try{
                        \$this->serviceCache->setDataForKey(\$key, \$data, $expires, \$meta, $optionsArrayString);
                    }
                    catch(\\Exception \$e) {

                        \$this->logger->info('The data store threw an exception : ' . \$e->getMessage());
                    }
                }

                return \$data;
            }
        ";

        return $method;
    }

    /**
     * [paramToDefaultValueString description]
     *
     * for use when building method calls in our proxy class 
     * 
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    private function paramToDefaultValueString($param) {

        // param has default value? 
        if($param->isOptional()) {

            $dv = $param->getDefaultValue();

            // most well-used types implemented but may require others 
            switch (getType($dv)) {
                case 'string':
                    return  "'$dv'";

                case 'boolean':
                    return ($dv ? 'true' : 'false');

                case 'integer':
                case 'double':
                    return "$dv";
                
                default:
                    return "null";
            }
        }

        return "null";
    }

    public function getMethodData(\ReflectionClass $r) {

        $methods = $r->getMethods();

        $methodData = array();

        foreach ($methods as $method) {

            // process annotations
            $docComment = $method->getDocComment();
            preg_match_all('#@(.*?)\n#s', $docComment, $annotations);
        
            $annotations = $annotations[1];

            if(is_array($annotations)) {

                $md = new MethodData();

                $md->setReflectionMethod($method);

                $keyElements    = array();
                $options        = array();

                foreach ($annotations as $a) {

                    $elements = explode(' ', $a);

                    switch ($elements[0]) {
                        case 'service-cache-enable':
                            $md->setServiceCacheEnabled(true);
                            break;

                        case 'service-cache-key':
                            if(isset($elements[1]) && isset($elements[2]))
                                $keyElements[] = new KeyElement($elements[1], $elements[2]);
                            else 
                                throw new ServiceProxyException("data-store-key annotation - missing elements");     
                            break;

                        case 'service-cache-option':
                            if(isset($elements[1]) && isset($elements[2]))
                                $options[$elements[1]] = $elements[2]; 
                            else 
                                throw new ServiceProxyException("service-cache-option annotation - missing elements");                             
                            break;

                        case 'service-cache-expires':
                            if(isset($elements[1]) && ((int) $elements[1] > 0))
                                $md->setExpires($elements[1]);
                            break;

                    }
                }

                // set any key elements we picked up
                $md->setKeyElements($keyElements);

                $md->setOptions($options);

                $methodData[] = $md;
            }
        }  

        return $methodData;       
    }

    /**
     * Get proxy filename from classname
     * @param  [type] $proxyClassName [description]
     * @return [type]                 [description]
     */
    private function getProxyFilename($proxyClassName) {

        return $this->proxyDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $proxyClassName) . '.php';        
    }

    /**
     * Generate our proxy class name (fully qualified namespace) from
     * our fully qualified traget class name
     * 
     * @param  [type] $targetClassName [description]
     * @return [type]                  [description]
     */
    private function getProxyClassName($targetClassName) {

        return $this->proxyNamespace . '\\' . $targetClassName . 'Proxy';
    }    
}