<?php

namespace App\Listener;

use App\Entity\AccessToken;
use App\Entity\Activity;
use App\Entity\AuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class TokenAuthentication
 * @package App\Listener
 */
class TokenAuthentication extends AbstractController
{
    private $request;
    private $authToken;
    private $accessToken;
    private $secret;
    private $endpoint;

    public function __construct(ParameterBagInterface $params)
    {
        $this->request = Request::createFromGlobals();
        $this->authToken = $this->request->headers->get('API-Authorization');
        $this->accessToken = $this->request->headers->get('ACCESS-Authorization');
        $this->secret = $params->get('TOKEN_SECRET');
        $this->endpoint = $this->request->server->get('REQUEST_URI');
    }

    /**
     * @param RequestEvent $event
     * @Catch ALL request except login.
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (($this->endpoint !== '/auth/login') && ($this->endpoint !== '/')
            && (!$this->startsWith($this->endpoint, '/_'))
            && (!$this->startsWith($this->endpoint, '/~'))) {
            try {
                if($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST)
                {
                    $this->checkToken();
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * @Check Token
     */
    public function checkToken()
    {
        $existingAuthTokens = $this->getDoctrine()->getRepository(AuthToken::class);
        $existingAccessTokens = $this->getDoctrine()->getRepository(AccessToken::class);
        $accessToken = $existingAccessTokens->findOneBy(['value' => "$this->accessToken"]);
        if (!($authToken = $this->authToken) || !($this->accessToken))
            throw new Exception('Token is missing...', 000002);
        if (!($authToken = $existingAuthTokens->findOneBy(['value' => $authToken])))
            throw new Exception('Unrecognized auth...', 000003);
        if (!$authToken->getAccessTokens()->contains($accessToken))
            throw new Exception('Unrecognized access...');
        if (!$accessToken->getAuthTokens()->contains($authToken))
            throw new Exception('Imcompatible tokens', 000007);
        else {
            try {
                /**
                 * @var AuthToken $authToken
                 * @var AccessToken $accessToken
                 */
                $authToken->isValid($this->secret);
                $accessToken->isValid($this->secret);
                $this->checkPrivileges($authToken, $accessToken);
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
    }

    /**
     * @param AuthToken $authToken
     * @param AccessToken $accessToken
     * @throws \Exception
     * @Check Privileges
     */
    public function checkPrivileges(AuthToken $authToken, AccessToken $accessToken)
    {
        $method = $this->request->getMethod();
        $endpoint = explode('/', $this->endpoint);
        $source = strtoupper($endpoint[1]);
        $segment = $endpoint[2];
        $storedCtl = $accessToken->getController() . 's';
        $action = explode('?',$endpoint[3])[0];
        $privileges = $authToken->getUser()->getPartner()->getPrivileges();
        /**
         * @Check Source
         */
        if(array_key_exists($source,$privileges))
        {
            /**
             * @Check Method
             */
            if (array_key_exists($method, $privileges[$source])) {
                /**
                 * @Check Segment
                 */
                if ((array_key_exists($segment, $privileges[$source][$method])) && (strtolower($segment) === strtolower($storedCtl))) {
                    /**
                     * @Check Action
                     */
                    if (is_numeric($action))
                        $action = "show";
                    if (!in_array($action, $privileges[$source][$method][$segment])) {
                        throw new Exception("You can't " . $action . " " . $segment, 000007);
                    } else {
                        $this->setActivity($authToken,$accessToken,$this->endpoint);
                    }
                } else {
                    throw new Exception("You can't access " . $segment, 000006);
                }
            } else {
                throw new Exception('Forbidden access...', 000005);
            }
        } else {
            throw new Exception('Forbidden '.$source.' access...', 000005);
        }
    }

    /**
     * @param AuthToken $authToken
     * @param AccessToken $accessToken
     * @param $endpoint
     * @throws \Exception
     * @Set Activity counter
     */
    public function setActivity(AuthToken $authToken,AccessToken $accessToken,$endpoint)
    {
        $request = Request::createFromGlobals();
        $now = (new \DateTime())->format('Y-m-d');
        $user = $authToken->getUser();
        $method = $request->getMethod();
        $source = strtoupper(explode('/', $endpoint)[1]);
        $controller = $accessToken->getController().'s';
        $action = explode('/', $endpoint)[3];
        if(is_numeric($action))
        {
            $action = 'show';
        }
        $em = $this->getDoctrine()->getManager();
         if(count($activities = $user->getActivities()) > 0)
         {
             foreach ($activities as $activity){
                 if(($activity->getDate()->format('Y-m-d') === $now)
                     && ($activity->getController() === $controller)
                     && ($activity->getMethod() === $method)
                     && ($activity->getAction() === $action)
                     && ($activity->getSource() === $source))
                 {
                     $activity->increment();
                 } else {
                     $activity = new Activity($user,$source,$method,$controller,$action);
                 }
             }
         } else {
             $activity = new Activity($user,$source,$method,$controller,$action);
         }
        $em->persist($activity);
        $em->flush();
    }

}