<?php


namespace App\Controller\Front;


use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiFrontController
 * @package App\Controller\Front
 * @Route("/_secure")
 */
class ApiFrontController extends AbstractController
{
    /**
     * @Route("/",name="api_front_dash")
     * @return Response
     */
    public function dashboard()
    {
        return $this->render('front/dash.html.twig');
    }

    /**
     * @Route("/users",name="api_front_users")
     */
    public function users(UserRepository $repository)
    {
        $users = $repository->findAll();
        return $this->render('front/users.html.twig', [
            'users' => $users
        ]);
    }
}