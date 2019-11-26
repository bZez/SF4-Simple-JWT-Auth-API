<?php


namespace App\Controller\Front;


use App\Entity\AuthToken;
use App\Entity\User;
use App\Form\UserCreationType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController
 * @package App\Controller\Front
 * @Route("/~private/users")
 */
class UserController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserRepository $repository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @Route("/",name="api_front_user")
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
     * @param User $user
     * @return Response
     * @throws \Exception
     * @Route("/delete/{user}",name="api_front_user_delete")
     */
    public function deleteUser(User $user)
    {
        if ($this->isGranted('ROLE_ADMIN_PARTNER'))
            if (($this->getUser()->getPartner() !== $user->getPartner()) || ($user === $user->getPartner()->getAdmin()))
                throw new \Exception('Hmmmm... Dangerous !');
        $em = $this->getDoctrine()->getManager();
        $authRepo = $this->getDoctrine()->getRepository(AuthToken::class);
        $auth = $authRepo->findOneBy(['user' => $user]);
        $user->setAuthToken(null);
        $em->persist($user);
        $em->flush();
        $em->remove($user);
        if ($auth !== null) {
            $em->remove($auth);
        }
        $em->flush();
        $this->addFlash('notice', 'User successfully deleted !');
        return $this->json(['Success !']);
    }

}