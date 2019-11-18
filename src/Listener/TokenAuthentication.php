<?php

namespace App\Listener;

use App\Entity\AccessToken;
use App\Entity\AuthToken;
use ReallySimpleJWT\Token as Tokenizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

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
        $this->endpoint = str_replace('index.php', '', $this->request->server->get('REDIRECT_URL'));
    }

    /**
     * @Catch ALL request except login.
     */
    public function onKernelRequest()
    {
        if (($this->endpoint !== '/auth/login') && ($this->endpoint !== '/')
            && (!$this->startsWith($this->endpoint, '/_'))
            && (!$this->startsWith($this->endpoint, '/~'))) {
            try {
                $this->checkToken();
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
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }

    /**
     * @param AuthToken $authToken
     * @param AccessToken $accessToken
     * @Check Privileges
     */
    public function checkPrivileges(AuthToken $authToken, AccessToken $accessToken)
    {
        $method = $this->request->getMethod();
        $endpoint = explode('/', $this->endpoint);
        $segment = $endpoint[1];
        $storedCtl = $accessToken->getController() . 's';
        $action = $endpoint[2];
        $privileges = $authToken->getUser()->getPartner()->getPrivileges();
        /**
         * @Check Method
         */
        if (array_key_exists($method, $privileges)) {
            /**
             * @Check Segment
             */
            if ((array_key_exists($segment, $privileges[$method])) && (strtolower($segment) === strtolower($storedCtl))) {
                /**
                 * @Check Action
                 */
                if (is_numeric($action))
                    $action = "show";
                if (!in_array($action, $privileges[$method][$segment])) {
                    throw new Exception("You can't " . $action . " " . $segment, 000007);
                }
            } else {
                throw new Exception("You can't access " . $segment, 000006);
            }
        } else {
            throw new Exception('Forbidden access...', 000005);
        }
    }
}