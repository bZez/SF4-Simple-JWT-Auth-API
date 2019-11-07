<?php


namespace App\Helper;


use App\Controller\Back\ApiBackController;

class Functions
{
    private $functions = [];

    public function __construct()
    {
        $f = new \ReflectionClass(new ApiBackController());
        foreach ($f->getMethods() as $m) {
            if ($m->class == $f->name) {
                $this->functions[] = $m->name;
            }
        }
    }

    public function getFunctions(): array
    {
        return $this->functions;
    }

}