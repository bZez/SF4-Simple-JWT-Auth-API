<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class RouterController extends AbstractController
{
    /**
     * @Route("/{segment}s/{action}", name="api_action")
     */
    public function forwardTo($segment,$action)
    {
        if (is_numeric($action))
            return $this->forward('App\Controller\\' . ucfirst($segment) . 'Controller::get', ['id' => $action]);
        else
            return $this->forward('App\Controller\\' . ucfirst($segment) . 'Controller::' . $action);
    }


    public function go()
    {
        //...
    }
}