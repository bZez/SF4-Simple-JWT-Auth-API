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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AuthController extends AbstractController
{
    /**
     * @param AuthToken $t
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
            /**
             * @var User $user
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
            $t = $tokenBuilder->setContentType('JWT')
                ->setHeaderClaim('Token','FirstAuthAPI')
                ->setSecret($this->getParameter('TOKEN_SECRET'))
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
        $request = Request::createFromGlobals();
        if (($login = $request->getUser()) && ($pwd = $request->getPassword())) {
            $user = $userRepository->findOneBy(['email' => $login]);
            if ($user) {
                if ($passwordEncoder->isPasswordValid($user, $pwd)) {
                    if (($userToken = $user->getAuthToken()) != null) {
                        try {
                            if ($this->isTokenValid($userToken)) {
                                return $this->json(['token' => $userToken->getValue()]);
                            } else {
                                return $this->json(['token' => $this->generateAuthToken($user)]);
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
        $token = Tokenizer::getPayload(Request::createFromGlobals()->headers->get('Authorization'),$this->getParameter('TOKEN_SECRET'));
        return $this->json([
            "User" => $token['user']['login'],
            "Rights" => $token['user']['privileges'],
            "Expire" => date('d.m.Y - H:i:s', $token['exp'])
        ]);
    }
}
