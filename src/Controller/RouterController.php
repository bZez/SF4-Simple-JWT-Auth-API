<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RouterController
 * @package App\Controller
 * @Route("/")
 */
class RouterController extends AbstractController
{
    /**
     * @param string $class
     * @param string $action
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{class}s/{action}", name="api_forwarder")
     */
    public function forwardTo($class,$action)
    {
        if (is_numeric($action))
            return $this->forward('App\Controller\\' . ucfirst($class) . 'Controller::show', ['id' => $action]);
        else
            return $this->forward('App\Controller\\' . ucfirst($class) . 'Controller::' . $action);
    }

}