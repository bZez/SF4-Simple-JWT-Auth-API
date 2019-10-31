<?php


namespace App\EventListener;


use App\Entity\AuthToken;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Token as Tokenizer;
use ReallySimpleJWT\Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class TokenAuthenticationListener extends AbstractController
{
    /**
     * @param AuthToken|object $t
     */
    public function isTokenValid($t)
    {
        $expireDate = strtotime(($t->getExpiration())->format('Y-m-d'));

//        $tokenExpireDate = JWT::decode($t->getValue(), $this->getParameter('TOKEN_SECRET'), array('HS256'))->exp;
        $tokenExpireDate = Tokenizer::getPayload($t->getValue(), $this->getParameter('TOKEN_SECRET'))['exp'];
        if ($expireDate !== $tokenExpireDate) {
            throw new Exception('Invalid or modified token...');
        }
    }

    /**
     * @return AuthToken|string
     */
    public function checkToken()
    {
        $request = Request::createFromGlobals();
        $existingTokens = $this->getDoctrine()->getRepository(AuthToken::class);
        $token = $request->headers->get('Authorization');
        //CHECK IF TOKEN EXIST IN DB
        if (($token = $existingTokens->findOneBy(['value' => $token]))) {
            $this->isTokenValid($token);
            $jwt = new Jwt($token->getValue(), $this->getParameter('TOKEN_SECRET'));
            return $jwt->getToken();
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