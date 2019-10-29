<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class RouterController extends AbstractController
{
    /**
     * @Route("/{segment}/{action}", name="api_get_action")
     */

    public function getFunction($segment, $action)
    {
        if (is_numeric($action))
            return $this->forward('App\Controller\\' . ucfirst($segment) . 'Controller::get', ['id' => $action]);
        else
            return $this->forward('App\Controller\\' . ucfirst($segment) . 'Controller::' . $action);
    }
}