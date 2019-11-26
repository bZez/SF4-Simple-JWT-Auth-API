<?php

namespace App\Controller\Routing;

use App\Entity\AccessToken;
use App\Entity\Activity;
use App\Entity\AuthToken;
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
     * @param string $source
     * @return Response
     * @Route("/{source}/{class}s/{action}", name="api_forwarder",methods={"GET|POST|PUT|DELETE"})
     */
    public function forwardTo($source,$class, $action)
    {
        if (is_numeric($action))
            return $this->forward('App\Controller\Data\\'.strtoupper($source).'\\' . ucfirst($class) . 'Controller::show', ['id' => $action]);
        else
            return $this->forward('App\Controller\Data\\'.strtoupper($source).'\\' . ucfirst($class) . 'Controller::' . $action);
    }

}