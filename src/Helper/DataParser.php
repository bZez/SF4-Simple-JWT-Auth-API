<?php


namespace App\Helper;


use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpKernel\KernelInterface;

class DataParser
{
    public $controllers;

    /**
     * DataParser constructor.
     * @param KernelInterface $kernel
     * @throws ReflectionException
     */
    public function __construct($kernel)
    {
        $dataDir = $kernel->getProjectDir() . '/src/Controller/Data/*';
        $dir = new \DirectoryIterator(dirname($dataDir));
        $this->controllers = [];
        $i = 0;
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $className = explode('.', $fileinfo->getFilename())[0];
                $f = new ReflectionClass('App\Controller\Data\\' . $className);
                $this->controllers[$i]['name'] = substr($className, 0, -10);
                foreach ($f->getMethods() as $id => $m) {
                    if ($m->class == $f->name) {
                        $this->controllers[$i]['methods'][] = $m->name;
                    }
                }
                $this->controllers[$i]['comments'][] = $f->getDocComment();
                $i++;
            }

        }
    }

    public function getControllers()
    {
        return $this->controllers;
    }
}