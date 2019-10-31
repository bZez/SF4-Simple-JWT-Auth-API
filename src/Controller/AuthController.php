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
    /**
     * @param Token $t
     */
    public function isTokenValid($t)
    {
        $expireDate = strtotime(($t->getExpiration())->format('Y-m-d'));
        $tokenExpireDate = Tokenizer::getPayload($t->getValue(),$this->getParameter('TOKEN_SECRET'))['exp'];
        if ($expireDate !== $tokenExpireDate) {
            throw new Exception('Invalid or modified token...');
        }
    }

    /**
     * @param $user
     * @return string|null
     * @throws \ReallySimpleJWT\Exception\ValidateException
     */
    public function generateAuthToken($user)
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
        $userInfos = [
            "login" => $user->getEmail(),
            "roles" => $user->getRoles(),
            "privileges" => $user->getPrivileges()
        ];
        //STORE TOKEN
        if ($user->getToken()) {
            $token = $user->getToken();
            return $token->getValue();
        } else {
            $token = new Token();
            $t = $tokenBuilder->setContentType('JWT')
                ->setHeaderClaim('Token','FirstAuthAPI')
                ->setSecret($this->getParameter('TOKEN_SECRET'))
                ->setIssuer('localhost')
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
        $user->setToken($token);
        $em->persist($token);
        $em->persist($user);
        $em->flush();
        return $token->getValue();
    }

    /**
     * @Route("/auth/login", name="api_get_auth",methods={"GET"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \ReallySimpleJWT\Exception\ValidateException
     */
    public function authenticate($request,$userRepository,$passwordEncoder)
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
                                return $this->json(['token' => $this->generateAuthToken($user)]);
                            }
                        } catch (\Exception $e) {
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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function user()
    {
        $token = Tokenizer::getPayload(Request::createFromGlobals()->headers->get('Authorization'),$this->getParameter('TOKEN_SECRET'));
        return $this->json([
            "User" => $token['user']['login'],
            "Rights" => $token['user']['privileges'],
            "Expire" => date('d.m.Y - H:i:s', $token['exp'])
        ]);
    }
}
