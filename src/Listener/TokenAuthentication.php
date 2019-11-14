<?php

namespace App\Listener;

use App\Entity\AccessToken;
use App\Entity\AuthToken;
use ReallySimpleJWT\Token as Tokenizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

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
        $this->endpoint = str_replace('index.php','',$this->request->server->get('REDIRECT_URL'));
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
                $this->checkPrivileges();
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
        if (!($authToken = $this->authToken) || !($accessToken = $this->accessToken))
            throw new Exception('Token is missing...', 000002);
        if (!($authToken = $existingAuthTokens->findOneBy(['value' => $authToken])) || !($accessToken = $existingAccessTokens->findOneBy(['value' => $accessToken])))
            throw new Exception('Unrecognized token...', 000003);
        if ($authToken !== $accessToken->getAuthToken())
            throw new Exception('Imcompatible tokens', 000007);
        else {
            try {
                $authToken->isValid($this->secret);
                $accessToken->isValid($this->secret);
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }

    /**
     * @Check Privileges
     */
    public function checkPrivileges()
    {
        $decoded = Tokenizer::getPayload($this->accessToken, $this->secret);
        $method = $this->request->getMethod();
        $endpoint = explode('/', $this->endpoint);
        $segment = $endpoint[1];
        $action = $endpoint[2];
        $privileges = $decoded['privileges'];
        /**
         * @Check Method
         */
        if (array_key_exists($method, $privileges)) {
            /**
             * @Check Segment
             */
            if (array_key_exists($segment, $privileges[$method])) {
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