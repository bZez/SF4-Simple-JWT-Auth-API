<?php


namespace App\Controller\Authentication;


use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccessController extends AbstractController
{
    private $request;
    private $secret;
    private $em;

    /**
     * AccessController constructor.
     * @param ParameterBagInterface $params
     * @param EntityManagerInterface $em
     */
    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em)
    {
        $this->secret = $params->get('TOKEN_SECRET');
        $this->request = Request::createFromGlobals();
        $this->em = $em;
    }

    /**
     * @param User $user
     * @return String Token
     * @Route("/_secure/generate/access/{user}",name="api_back_generate_access")
     */
    public function generateAccessToken(User $user)
    {
        $authToken = $user->getAuthToken();
        if ($authToken->getAccessToken())
            $accessToken = $authToken->getAccessToken();
        else
            $accessToken = new AccessToken($authToken);
        $this->em->persist($accessToken);
        $authToken->setAccessToken($accessToken);
        $this->em->persist($authToken);
        $this->em->flush();
        return $this->json([$accessToken->getValue()]);
    }
}