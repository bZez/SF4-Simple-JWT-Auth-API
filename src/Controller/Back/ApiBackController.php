<?php


namespace App\Controller\Back;


use App\Repository\PartnerRepository;
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
     * @Route("/users",name="api_back_user")
     */
    public function user(UserRepository $repository)
    {
        $users = $repository->findAll();
        return $this->render('back/user.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/partners",name="api_back_partner")
     */
    public function partner(PartnerRepository $repository)
    {
        $partners = $repository->findAll();
        return $this->render('back/partner.html.twig', [
            'partners' => $partners
        ]);
    }
}