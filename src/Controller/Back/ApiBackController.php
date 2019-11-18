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
     * @param Request $request
     * @param UserRepository $repository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @Route("/users",name="api_back_user")
     */
    public function user(Request $request, UserRepository $repository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $createForm = $this->createForm(UserCreationType::class, $user);
        $createForm->handleRequest($request);
        $users = $repository->findAll();
        $em = $this->getDoctrine()->getManager();
        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $user->setEmail($user->getEmail());
            $user->setFirstName($user->getFirstName());
            $user->setLastName($user->getLastName());
            $user->setPartner($user->getPartner());
            $user->setRoles($user->getRoles());
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
            $em->persist($user);
            $em->flush();

            $this->addFlash('notice', 'User successfully created !');
            return $this->redirectToRoute('api_back_user');
        }
        return $this->render('back/user.html.twig', [
            'users' => $users,
            'createForm' => $createForm->createView()
        ]);
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

    /**
     * @param PartnerRepository $repository
     * @return Response
     * @Route("/partners",name="api_back_partner")
     */
    public function partner(PartnerRepository $repository)
    {
        $partners = $repository->findAll();
        return $this->render('back/partner.html.twig', [
            'partners' => $partners,
            'controllers' => $this->controllers
        ]);
    }
}