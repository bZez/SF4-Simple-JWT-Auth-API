<?php


namespace App\Controller\Front;


use App\Helper\DataParser;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use \ReflectionException;
use ReallySimpleJWT\Token as Tokenizer;

/**
 * Class ApiFrontController
 * @package App\Controller\Front
 * @Route("/~private")
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

    /**
     * @Route("/datas",name="api_front_datas")
     * @throws ReflectionException
     */
    public function datas(KernelInterface $kernel)
    {
        $parser = new DataParser($kernel);
        $controllers = $parser->getControllers();
        return $this->render('front/data.html.twig', [
            'controllers' => $controllers,
        ]);
    }
}