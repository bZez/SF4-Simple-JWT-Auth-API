<?php


namespace App\EventListener;


use App\Entity\AuthToken;

use ReallySimpleJWT\Token as Tokenizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class TokenAuthenticationListener extends AbstractController
{
    private $request;
    private $authToken;
    private $secret;

    public function __construct(ParameterBagInterface $params)
    {
        $this->request = Request::createFromGlobals();
        $this->authToken = $this->request->headers->get('Authorization');
        $this->secret = $params->get('TOKEN_SECRET');
    }

    public function checkToken()
    {
        $existingTokens = $this->getDoctrine()->getRepository(AuthToken::class);
        if (!($token = $this->authToken))
            throw new Exception('Token is missing...');
        if (!($token = $existingTokens->findOneBy(['value' => $token])))
            throw new Exception('Unrecognized token...');
        else {
            try {
                $token->isValid($this->secret);
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }

    }

    /**
     * @Infos Check if the requested methods is enabled in authToken.
     */
    public function checkPrivileges()
    {
        $request = $this->request;
        $privileges = Tokenizer::getPayload($this->authToken, $this->secret)['user']['privileges'];
        if (!in_array($request->getMethod(), $privileges)) {
            throw new Exception('Forbidden access...');
        }
    }

    public function onKernelRequest()
    {
        if ($this->request->server->get('REDIRECT_URL') !== '/auth/login') {
            try{
                $this->checkToken();
                $this->checkPrivileges();
            } catch (Exception $e)
            {
                die($e->getMessage());
            }
        }
    }
}