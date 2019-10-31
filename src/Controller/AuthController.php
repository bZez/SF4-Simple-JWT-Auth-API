<?php

namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use ReallySimpleJWT\Build;
use ReallySimpleJWT\Encode;
use ReallySimpleJWT\Token as Tokenizer;
use App\Repository\UserRepository;
use ReallySimpleJWT\Validate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AuthController extends AbstractController
{
    public function isTokenValid($t)
    {
        /**
         * @var Token $t
         */
        $expireDate = strtotime(($t->getExpiration())->format('Y-m-d'));

//        $tokenExpireDate = JWT::decode($t->getValue(), $this->getParameter('TOKEN_SECRET'), array('HS256'))->exp;
        $tokenExpireDate = Tokenizer::getPayload($t->getValue(),$this->getParameter('TOKEN_SECRET'))['exp'];
        if ($expireDate !== $tokenExpireDate) {
            throw new Exception('Invalid or modified token...');
        }
    }

    public function generateToken($user)
    {
        $tokenBuilder = new Build('JWT', new Validate(), new Encode());
        /**
         * @var User $user
         * @var Token $token
         */
        //DATES
        $tomorow = new \DateTime('now +1year');
        $exp = strtotime($tomorow->format('Y-m-d'));

        //DOCTRINE
        $em = $this->getDoctrine()->getManager();
        //SET TOKEN PARAMS
        /**
         * Not before...
         */
        // $nbf = strtotime('2021-01-01 00:00:01');

        /**
         * Expire at....
         */
        $payloadArray = array();
        $payloadArray['user'] = [
            "login" => $user->getEmail(),
            "roles" => $user->getRoles(),
            "privileges" => $user->getPrivileges()
        ];
        if (isset($nbf)) {
            $payloadArray['nbf'] = $nbf;
        }
        if (isset($exp)) {
            $payloadArray['exp'] = $exp;
        }

        //STORE TOKEN
        if ($user->getToken()) {
            $token = $user->getToken();
            return $token->getValue();
        } else {
            $token = new Token();
            $t = $tokenBuilder->setContentType('JWT')
                ->setHeaderClaim('Token','MGELAPI')
                ->setSecret($this->getParameter('TOKEN_SECRET'))
                ->setIssuer('localhost')
                ->setSubject('api-access-token')
                ->setAudience('https://api.mgel.fr')
                ->setExpiration($exp)
                ->setIssuedAt(time())
                ->setJwtId(md5(uniqid('TOKEN')))
                ->setPayloadClaim('user',$payloadArray['user'])
                ->build();
        }
        $token->setUser($user);
//        $token->setValue(JWT::encode($payloadArray, $this->getParameter('TOKEN_SECRET')));
        $token->setValue($t->getToken());//Tokenizer::customPayload($payloadArray,$this->getParameter('TOKEN_SECRET')));
        $token->setExpiration($tomorow);
        $user->setToken($token);
        $em->persist($token);
        $em->persist($user);
        $em->flush();




        return $token->getValue();
    }

    /**
     * @Route("/auth/login", name="api_get_auth",methods={"GET"})
     */
    public function authenticate(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (($login = $request->getUser()) && ($pwd = $request->getPassword())) {
            $user = $userRepository->findOneBy(['email' => $login]);
            if ($user) {
                if ($passwordEncoder->isPasswordValid($user, $pwd)) {
                    if (($userToken = $user->getToken()) != null) {
                        try {
                            if ($this->isTokenValid($userToken)) {
                                return $this->json(['token' => $userToken->getValue()]);
                            } else {
                                return $this->json(['token' => $this->generateToken($user)]);
                            }
                        } catch (\Exception $e) {
                            return $this->json(['Error' => $e->getMessage()]);
                        }
                    } else {
                        return $this->json(['token' => $this->generateToken($user)]);
                    }
                } else {
                    return $this->json(['Error' => "Invalid credentials...."], 500);
                }
            } else {
                return $this->json(['Error' => "Unable to find user...."], 500);
            }
        } else {
            return $this->json(['Error' => 'You must login to access API...'], 500);
        }
    }

    /**
     * @Route("/auth/user", name="api_get_user")
     */
    public function user()
    {
//        $token =  JWT::decode(Request::createFromGlobals()->headers->get('Authorization'), $this->getParameter('TOKEN_SECRET'), array('HS256'));
        $token = Tokenizer::getPayload(Request::createFromGlobals()->headers->get('Authorization'),$this->getParameter('TOKEN_SECRET'));
        return $this->json([
            "User" => $token['user']['login'],
            "Rights" => $token['user']['privileges'],
            "Expire" => date('d.m.Y - H:i:s', $token['exp'])
        ]);
    }
}
