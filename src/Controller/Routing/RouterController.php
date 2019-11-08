<?php

namespace App\Controller\Routing;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RouterController
 * @package App\Controller\Routing
 * @Route("/", name="api_router")
 */
class RouterController extends AbstractController
{
    /**
     * @param string $class
     * @param string $action
     * @return Response
     * @Route("/{class}s/{action}", name="api_forwarder")
     */
    public function forwardTo($class,$action)
    {
        if (is_numeric($action))
            return $this->forward('App\Controller\Data\\' . ucfirst($class) . 'Controller::show', ['id' => $action]);
        else
            return $this->forward('App\Controller\Data\\' . ucfirst($class) . 'Controller::' . $action);
    }

}