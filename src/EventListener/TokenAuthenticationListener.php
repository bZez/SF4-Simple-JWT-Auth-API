<?php


namespace App\EventListener;


use App\Entity\Token;
use App\Inc\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class TokenAuthenticationListener extends AbstractController
{
    public function isTokenValid($t)
    {
        /**
         * @var Token $t
         */
        $expireDate = strtotime(($t->getExpiration())->format('Y-m-d'));

        $tokenExpireDate = JWT::decode($t->getValue(), $this->getParameter('TOKEN_SECRET'), array('HS256'))->exp;
        if ($expireDate !== $tokenExpireDate) {
            throw new Exception('Invalid or modified token...');
        }
    }

    public function checkToken()
    {
        $request = Request::createFromGlobals();
        $existingTokens = $this->getDoctrine()->getRepository(Token::class);
        $token = $request->headers->get('Authorization');
        //CHECK IF TOKEN EXIST IN DB
        if (($token = $existingTokens->findOneBy(['value' => $token]))) {
            $this->isTokenValid($token);
            return JWT::decode($token->getValue(), $this->getParameter('TOKEN_SECRET'), array('HS256'));
        } else {
            throw new Exception('Unrecognized token...');
        }
    }

    public function onKernelRequest()
    {
        $request = Request::createFromGlobals();
        if ($request->server->get('REDIRECT_URL') !== '/auth/login')
            $this->checkToken();
    }
}