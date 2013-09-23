<?php
namespace Epiphany\ServiceCacheBundle\Proxy;

class FileSystem
{
    public function __construct() {

    }

    public function fileExists($filename) {

        return file_exists($filename);
    }

    public function filePutContents($filename, $contents) {

        // ensure the directory exists, and if not, then create
        $dirs = explode(DIRECTORY_SEPARATOR, substr($filename, 1));

        $path = '';

        foreach ($dirs as $key => $dir) {

            // ignore filename
            if(($key + 1) == count($dirs))
                break;

            $path .= '/' . $dir;

            if(!file_exists($path)) {

                mkdir($path);
            }
        }

        return file_put_contents($filename, $contents);        
    }

    public function requireFile($filename) {

        require $filename;
    }
}