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
                $c = new ReflectionClass('App\Controller\Data\\' . $className);
                $this->controllers[$i]['name'] = substr($className, 0, -10);
                foreach ($c->getMethods() as $id => $m) {
                    if ($m->class == $c->name) {
                        $this->controllers[$i]['methods'][] = [
                            'name' => $m->name,
                            'comments' => $this->parseComments($c, $m)
                        ];
                    }
                }
                $i++;
            }

        }
    }

    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public function parseComments($c, $m)
    {
        $haystack = $c->getMethod($m->name)->getDocComment();
        $start = "/**       ";
        $end = "     */";
        $haystack = str_replace($start, "\r\n", $haystack);
        $haystack = str_replace($end, "", $haystack);
        $haystack = str_replace('     *', "", $haystack);
        $separator = "\r\n";
        $line = strtok($haystack, $separator);
        $comments = [];
        while (!is_bool($line)) {
            # do something with $line
            $line = strtok($separator);
            if (is_string($line))
                $comments[] = $line;
        }
        return $comments;
    }

    public function getControllers()
    {
        return $this->controllers;
    }
}