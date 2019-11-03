<?php

namespace App\EventListener;

use App\Entity\AuthToken;
use ReallySimpleJWT\Token as Tokenizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class TokenAuthentication extends AbstractController
{
    private $request;
    private $authToken;
    private $secret;
    private $decoded;
    private $user;
    private $endpoint;

    public function __construct(ParameterBagInterface $params)
    {
        $this->request = Request::createFromGlobals();
        $this->authToken = $this->request->headers->get('Authorization');
        $this->secret = $params->get('TOKEN_SECRET');
        $this->decoded = Tokenizer::getPayload($this->authToken, $this->secret);
        $this->user = $this->decoded['user'];
        $this->endpoint = $this->request->server->get('REDIRECT_URL');
    }

    /**
     * @Check Token
     */
    public function checkToken()
    {
        $existingTokens = $this->getDoctrine()->getRepository(AuthToken::class);
        if (!($token = $this->authToken))
            throw new Exception('Token is missing...', 000002);
        if (!($token = $existingTokens->findOneBy(['value' => $token])))
            throw new Exception('Unrecognized token...', 000003);
        else {
            try {
                $token->isValid($this->secret);
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
        $method = $this->request->getMethod();
        $endpoint = explode('/', $this->endpoint);
        $segment = $endpoint[1];
        $action = $endpoint[2];
        $privileges = $this->user['privileges'];
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
                if (!in_array($action, $privileges[$method][$segment])) {
                    throw new Exception("You can't " . $action . " " . $segment, 000006);
                }
            } else {
                throw new Exception("You can't access '" . $segment, 000005);
            }
        } else {
            throw new Exception('Forbidden access...', 000004);
        }
    }

    /**
     * @Catch ALL request except login.
     */
    public function onKernelRequest()
    {
        if ($this->endpoint !== '/auth/login') {
            try {
                $this->checkToken();
                $this->checkPrivileges();
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }
}