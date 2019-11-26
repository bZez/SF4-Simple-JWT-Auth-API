<?php


namespace App\Controller\Authentication;


use App\Entity\AccessRequest;
use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @param $controller string
     * @param $source string
     * @return String Token
     * @Route("/_secure/generate/access/{source}/{controller}/{user}",name="api_back_generate_access")
     */
    public function generateAccessToken(User $user,$source,$controller)
    {
        $authToken = $user->getAuthToken();
        $users = $user->getPartner()->getUsers();
        $accessTokens = $authToken->getAccessTokens();
        if(count($accessTokens) > 0){
            foreach ($accessTokens as $aT) {
                if($aT->getController() !== $controller)
                {
                    $accessToken = new AccessToken($authToken,$source,$controller);
                }
            }
        } else {
            $accessToken = new AccessToken($authToken,$source,$controller);
        }

        foreach ($users as $user) {
            if($authToken = $user->getAuthToken())
            {
                $accessToken->addAuthToken($authToken);
                $this->em->persist($accessToken);
                $authToken->addAccessToken($accessToken);
                $this->em->persist($authToken);
            }
        }
        $this->em->flush();
        return $this->json([$accessToken->getValue()]);
    }

    /**
     * @param User $user
     * @param $controller
     * @param $source
     * @Route("/~private/request/access/{source}/{controller}/{user}",name="api_front_request_access")
     * @return JsonResponse
     */
    public function requestAccess(User $user,$source,$controller)
    {
        $accessReq = new AccessRequest($user,$source,$controller);
        $this->em->persist($accessReq);
        $this->em->flush();
        return $this->json(["Access request registered !"]);
    }
}