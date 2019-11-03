<?php

namespace App\Controller;

use App\Entity\AuthToken;
use App\Entity\User;
use ReallySimpleJWT\Build;
use ReallySimpleJWT\Encode;
use ReallySimpleJWT\Exception\ValidateException;
use ReallySimpleJWT\Token as Tokenizer;
use App\Repository\UserRepository;
use ReallySimpleJWT\Validate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AuthController extends AbstractController
{
    private $request;
    private  $secret;

    public function __construct(ParameterBagInterface $params)
    {
        $this->secret = $params->get('TOKEN_SECRET');
        $this->request =  Request::createFromGlobals();
    }

    /**
     * @param AuthToken $t
     * @throws Exception
     */
    public function isTokenValid($t)
    {
        $expireDate = strtotime(($t->getExpiration())->format('Y-m-d'));
        $tokenExpireDate = Tokenizer::getPayload($t->getValue(),$this->secret)['exp'];
        if ($expireDate !== $tokenExpireDate) {
            throw new Exception('Invalid or modified token...',000001);
        }
    }

    /**
     * @param User $user
     * @return AuthToken|string $token
     * @throws ValidateException
     */
    public function generateAuthToken($user)
    {
        //CHECK OR STORE TOKEN
        if ($user->getAuthToken()) {
            $token = $user->getAuthToken();
            return $token->getValue();
        } else {
            $tokenBuilder = new Build('JWT', new Validate(), new Encode());
            $token = new AuthToken();
            //DATES
            $tomorow = new \DateTime('now +1year');
            $exp = strtotime($tomorow->format('Y-m-d'));

            //DOCTRINE
            $em = $this->getDoctrine()->getManager();


            //SET TOKEN PARAMS
            $userInfos = [
                "login" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "privileges" => $user->getPrivileges()
            ];
            $t = $tokenBuilder->setContentType('JWT')
                ->setHeaderClaim('Token','FirstAuthAPI')
                ->setSecret($this->secret)
                ->setIssuer('API Authenticator')
                ->setSubject('api-access-token')
                ->setAudience('https://yourapi.com')
                ->setExpiration($exp)
                ->setIssuedAt(time())
                ->setJwtId(md5(uniqid('TOKEN')))
                ->setPayloadClaim('user',$userInfos)
                ->build();
        }
        $token->setUser($user);
        $token->setValue($t->getToken());
        $token->setExpiration($tomorow);
        $user->setAuthToken($token);
        $em->persist($token);
        $em->persist($user);
        $em->flush();
        return $token->getValue();
    }

    /**
     * @Route("/auth/login", name="api_get_auth",methods={"GET"})
     * @return JsonResponse
     * @throws \Exception
     */
    public function authenticate(UserRepository $userRepository,UserPasswordEncoderInterface $passwordEncoder)
    {
        $request = $this->request;
        if (($login = $request->getUser()) && ($pwd = $request->getPassword())) {
            $user = $userRepository->findOneBy(['email' => $login]);
            if ($user) {
                if ($passwordEncoder->isPasswordValid($user, $pwd)) {
                    if (($userToken = $user->getAuthToken()) != null) {
                        try {
                            if ($this->isTokenValid($userToken)) {
                                return $this->json(['authToken' => $userToken->getValue()]);
                            } else {
                                return $this->json(['authToken' => $this->generateAuthToken($user)]);
                            }
                        } catch (Exception $e) {
                            return $this->json(['Error' => $e->getMessage()]);
                        }
                    } else {
                        return $this->json(['token' => $this->generateAuthToken($user)]);
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
     * @Route("/auth/user", name="api_get_user",methods={"GET"})
     * @return JsonResponse
     */
    public function user()
    {
        $token = Tokenizer::getPayload($this->request->headers->get('Authorization'),$this->secret);
        return $this->json([
            "User" => $token['user']['login'],
            "Rights" => $token['user']['privileges'],
            "Expire" => date('d.m.Y - H:i:s', $token['exp'])
        ]);
    }
}
