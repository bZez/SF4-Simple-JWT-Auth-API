<?php


namespace App\Controller\Back;


use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiBackController
 * @package App\Controller\Back
 * @Route("/_secure")
 */
class ApiBackController extends AbstractController
{
    /**
     * @Route("/",name="api_back_dash")
     * @return Response
     */
    public function dashboard()
    {
        return $this->render('back/dash.html.twig');
    }

    /**
     * @Route("/users",name="api_back_users")
     */
    public function users(UserRepository $repository)
    {
        $users = $repository->findAll();
        return $this->render('back/users.html.twig', [
            'users' => $users
        ]);
    }
}