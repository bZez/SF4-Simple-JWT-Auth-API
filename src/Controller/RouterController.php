<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class RouterController extends AbstractController
{

    public function forwardTo($segment,$action)
    {
        if (is_numeric($action))
            return $this->forward('App\Controller\\' . ucfirst($segment) . 'Controller::get', ['id' => $action]);
        else
            return $this->forward('App\Controller\\' . ucfirst($segment) . 'Controller::' . $action);
    }

    /**
     * @Route("/{segment}s/{action}", name="api_action")
     */
    public function go()
    {
        $request = Request::createFromGlobals();

        if ($request->isMethod('GET')) {
            //GET
        }

        if ($request->isMethod('POST')) {
            //POST
        }

        if ($request->isMethod('PUT')) {
            //PUT
        }

        if ($request->isMethod('DELETE')) {
            //DELETE
        }


    }
}