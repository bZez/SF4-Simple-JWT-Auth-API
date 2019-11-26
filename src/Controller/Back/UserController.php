<?php


namespace App\Controller\Back;


use App\Entity\AuthToken;
use App\Entity\User;
use App\Form\UserCreationType;
use App\Helper\DataParser;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiBackController
 * @package App\Controller\Back
 * @Route("/_secure/users")
 */
class UserController extends AbstractController
{
    private $controllers;

    /**
     * UserController constructor.
     * @param KernelInterface $kernel
     * @throws \ReflectionException
     */
    public function __construct(KernelInterface $kernel)
    {
        $parser = new DataParser($kernel);
        $this->controllers = $parser->getControllers();
    }

    /**
     * @param Request $request
     * @param UserRepository $repository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @Route("/",name="api_back_user")
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

}