<?php


namespace App\EventListener;


use App\Entity\AuthToken;

use ReallySimpleJWT\Token as Tokenizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class TokenAuthenticationListener extends AbstractController
{
    private $request;
    private $authToken;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
        $this->authToken = $this->request->headers->get('Authorization');
    }

    /**
     * @return AuthToken|string
     */
    public function checkToken()
    {

        $existingTokens = $this->getDoctrine()->getRepository(AuthToken::class);
        if (!($token = $this->authToken))
            throw new Exception('Token is missing...');
        if (!($token = $existingTokens->findOneBy(['value' => $token])))
            throw new Exception('Unrecognized token...');
        else
            $token->isValid();

    }

    /**
     * @Infos Check if the requested methods is enabled in authToken.
     */
    public function checkPrivileges()
    {
        $request = $this->request;
        $privileges = Tokenizer::getPayload($this->authToken, $this->getParameter('TOKEN_SECRET'))['user']['privileges'];
        if (!in_array($request->getMethod(), $privileges)) {
            throw new Exception('Forbidden access...');
        }
    }

    public function onKernelRequest()
    {

        if ($this->request->server->get('REDIRECT_URL') !== '/auth/login') {
            $this->checkToken();
            $this->checkPrivileges();
        }
    }
}