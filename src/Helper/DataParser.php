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
     * @author  Sam BZEZ <sam@bzez.dev>
     */
    public function __construct($kernel = null)
    {
        if ($kernel) {
            $part = new \DirectoryIterator(dirname($kernel->getProjectDir() . '/src/Controller/Data/*'));
            foreach ($part as $f) {
                if ($f->isDir() && !$f->isDot()) {
                    $dataDir = $kernel->getProjectDir() . '/src/Controller/Data/' . $f . '/*';
                    $dir = new \DirectoryIterator(dirname($dataDir));
                    $this->controllers = [];
                    $i = 0;
                    foreach ($dir as $fileinfo) {
                        if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
                            $className = explode('.', $fileinfo->getFilename())[0];
                            $c = new ReflectionClass('App\Controller\Data\\' . $f . '\\' . $className);
                            $this->controllers[$i]['name'] = substr($className, 0, -10);
                            $this->controllers[$i]['source'] = "$f";
                            $doc = str_replace('*', '', $c->getDocComment());
                            $doc = str_replace('/', '', $doc);
                            $doc = str_replace('\\', '', $doc);
                            $this->controllers[$i]['doc'] = $doc;
                            foreach ($c->getMethods() as $id => $m) {
                                if (($m->class == $c->name) && !$this->startsWith($m->name, '__')) {
                                    $this->controllers[$i]['methods'][] = [
                                        'name' => $m->name,
                                        'endpoint' => $this->parseMethodComments($c, $m)['endpoint'],
                                        'method' => $this->parseMethodComments($c, $m)['method'],
                                        'response' => $this->parseMethodComments($c, $m)['response'],
                                        'params' => $this->parseMethodComments($c, $m)['params'],
                                        'infos' => $this->parseMethodComments($c, $m)['infos']
                                    ];
                                }
                            }
                            $i++;
                        }
                    }
                }
            }

        }
    }

    public function parseMethodComments($c, $m)
    {
        $haystack = $c->getMethod($m->name)->getDocComment();
        $start = "/**       ";
        $end = "     */";
        $haystack = str_replace($start, "\r\n", $haystack);
        $haystack = str_replace($end, "", $haystack);
        $haystack = str_replace('     *', "", $haystack);
        $separator = "\r\n";
        $line = strtok($haystack, $separator);
        $endpoint = '';
        $method = '';
        $response = '';
        $infos = '';
        $params = [];
        while (!is_bool($line)) {
            # do something with $line
            $line = strtok($separator);
            if (is_string($line)) {
                if ($this->startsWith($line, ' @param')) {
                    $p = explode(' ', str_replace(' @param ', '', $line));
                    if((isset($p[1]))&&(isset($p[2])))
                    {
                        $params[] = [
                            "name" => $p[0],
                            "val" => $p[1],
                            "desc" => $p[2]
                        ];
                    } else {
                        $params[] = [
                            "name" => $p[0],
                            "val" => '',
                            "desc" => ''
                        ];
                    }
                }
                if ($this->startsWith($line, ' @api')) {
                    $endpoint = str_replace(' @api ', '', $line);
                }
                if ($this->startsWith($line, ' @return')) {
                    $response = str_replace(' @return ', '', $line);
                }
                if ($this->startsWith($line, ' @method')) {
                    $method = str_replace(' @method ', '', $line);
                }
                if ($this->startsWith($line, ' @example')) {
                    $infos = str_replace(' @example ', '', $line);
                }
            }
        }
        $doc = [
            'endpoint' => $endpoint,
            'method' => $method,
            'response' => $response,
            'params' => $params,
            'infos' => $infos
        ];
        return $doc;
    }

    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public function getControllers()
    {
        return $this->controllers;
    }

    public function getMethods($source, $controller)
    {
        try {
            $c = new ReflectionClass('App\Controller\Data\\' . $source . '\\' . $controller . 'Controller');
        } catch (\Exception $e) {
            return die;
        }
        $methods['GET'] = [];
        $methods['POST'] = [];
        $methods['PUT'] = [];
        $methods['DELETE'] = [];
        foreach ($c->getMethods() as $id => $m) {
            if (($m->class == $c->name) && !$this->startsWith($m->name, '__')) {
                $methods[$this->parseMethodComments($c, $m)['method']][] .= $m->name;
            }
        }
        return $methods;
    }
}