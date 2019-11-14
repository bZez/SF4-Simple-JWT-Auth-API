<?php


namespace App\Controller\Front;


use App\Entity\User;
use App\Form\UserCreationType;
use App\Helper\DataParser;
use App\Repository\PartnerRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use \ReflectionException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @Route("/p/{partner}",name="api_front_partner")
     * @return Response
     */
    public function partner($partner,PartnerRepository $repository)
    {
        $partner = $repository->findOneBy(['name'=>$partner]);
        return $this->render('front/partner.html.twig',
            [
                'partner' =>$partner
            ]);
    }

    /**
     * @param Request $request
     * @param UserRepository $repository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @Route("/users",name="api_front_user")
     */
    public function user(Request $request, UserRepository $repository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $createForm = $this->createForm(UserCreationType::class, $user);
        $createForm->handleRequest($request);
        $users = $repository->findBy(['partner' => $this->getUser()->getPartner()]);
        $em = $this->getDoctrine()->getManager();
        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $user->setEmail($user->getEmail());
            $user->setFirstName($user->getFirstName());
            $user->setLastName($user->getLastName());
            $user->setPartner($this->getUser()->getPartner());
            $user->setRoles(['ROLE_USER_PARTNER']);
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
            $em->persist($user);
            $em->flush();

            $this->addFlash('notice', 'User successfully created !');
            return $this->redirectToRoute('api_front_user');
        }
        return $this->render('front/user.html.twig', [
            'users' => $users,
            'createForm' => $createForm->createView()
        ]);
    }

    /**
     * @param KernelInterface $kernel
     * @return Response
     * @throws ReflectionException
     * @Route("/datas",name="api_front_data")
     */
    public function data(KernelInterface $kernel)
    {
        $parser = new DataParser($kernel);
        $controllers = $parser->getControllers();
        return $this->render('front/data.html.twig', [
            'controllers' => $controllers,
        ]);
    }
}