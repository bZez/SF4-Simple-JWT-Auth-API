<?php


namespace App\Controller\Back;


use App\Entity\User;
use App\Form\UserCreationType;
use App\Helper\DataParser;
use App\Repository\AccessRequestRepository;
use App\Repository\PartnerRepository;
use App\Repository\UserRepository;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class ApiBackController
 * @package App\Controller\Back
 * @Route("/_secure")
 */
class ApiBackController extends AbstractController
{
    private $controllers;

    public function __construct(KernelInterface $kernel)
    {
        $parser = new DataParser($kernel);
        $this->controllers = $parser->getControllers();
    }

    /**
     * @Route("/",name="api_back_dash")
     * @return Response
     */
    public function dashboard()
    {
        return $this->render('back/dash.html.twig');
    }

    /**
     * @return Response
     * @Route("/datas",name="api_back_data")
     */
    public function data()
    {
        return $this->render('front/data.html.twig', [
            'controllers' => $this->controllers,
        ]);
    }

    /**
     * @param AccessRequestRepository $repository
     * @return Response
     * @Route("/requests",name="api_back_request")
     */
    public function request(AccessRequestRepository $repository)
    {
        $requests = $repository->findAll();
        return $this->render('back/request.html.twig', [
            'requests' => $requests,
        ]);
    }


}