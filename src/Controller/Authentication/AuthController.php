<?php

namespace App\Controller\Authentication;

use App\Entity\AuthToken;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Token as Tokenizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class AuthController
 * @package App\Controller\Authentication
 */
class AuthController extends AbstractController
{
    private $request;
    private $secret;
    private $em;

    /**
     * AuthController constructor.
     * @var ParameterBagInterface $params
     * @var EntityManagerInterface $em
     */
    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em)
    {
        $this->secret = $params->get('TOKEN_SECRET');
        $this->request = Request::createFromGlobals();
        $this->em = $em;
    }

    /**
     * @param User $user
     * @return string|null
     */
    public function generateAuthToken(User $user)
    {
        if ($user->getAuthToken()) {
            $token = $user->getAuthToken();
            return $token->getValue();
        } else {
            $token = new AuthToken($user);
        }
        $user->setAuthToken($token);
        $this->em->persist($token);
        $this->em->persist($user);
        $this->em->flush();
        return $token->getValue();
    }

    /**
     * @Route("/auth/login", name="api_get_auth",methods={"GET"})
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     *@throws Exception
     */
    public function authenticate(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $request = $this->request;
        if (($login = $request->getUser()) && ($pwd = $request->getPassword())) {
            $user = $userRepository->findOneBy(['email' => $login]);
            if ($user) {
                if ($passwordEncoder->isPasswordValid($user, $pwd)) {
                    if (($userToken = $user->getAuthToken()) != null) {
                        try {
                            if ($userToken->isValid($this->secret)) {
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
        $token = Tokenizer::getPayload($this->request->headers->get('Authorization'), $this->secret);
        return $this->json([
            "User" => $token['user']['login'],
            "Rights" => $token['user']['privileges'],
            "Expire" => date('d.m.Y - H:i:s', $token['exp'])
        ]);
    }

    /**
     * @Route("/test")
     */
    public function test()
    {
        $API_PUBLIC_KEY = "";
        $API_PRIVATE_KEY = "";
        $opts = [
            'http' => [
                'method' => "GET",
                'header' => "API-Authorization: $API_PUBLIC_KEY\r\n" .
                    "ACCESS-Authorization: $API_PRIVATE_KEY\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $endpoint = file_get_contents('http://api.jwt/users/list', false, $context);
        $response = json_decode($endpoint);

        return new JsonResponse($response);
    }
}
