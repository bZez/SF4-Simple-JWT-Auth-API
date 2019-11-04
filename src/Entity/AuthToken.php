<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use ReallySimpleJWT\Build;
use ReallySimpleJWT\Encode;
use ReallySimpleJWT\Exception\ValidateException;
use ReallySimpleJWT\Token as Tokenizer;
use ReallySimpleJWT\Validate;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuthTokenRepository")
 */
class AuthToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=4096)
     */
    private $value;

    /**
     * @ORM\Column(type="date")
     */
    private $creation;

    /**
     * @ORM\Column(type="date")
     */
    private $expiration;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ip;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\AccessToken", mappedBy="authToken")
     * @ORM\JoinColumn(nullable=true)
     */
    private $accessToken;


    public function __construct($user)
    {
        $tokenBuilder = new Build('JWT', new Validate(), new Encode());
        $this->creation = new DateTime();
        $this->expiration = $this->creation->modify("+1year");
        $exp = strtotime($this->expiration->format('Y-m-d'));
        $userInfos = [
            "login" => $user->getEmail(),
            "roles" => $user->getRoles()
        ];
        try {
            $t = $tokenBuilder->setContentType('JWT')
                ->setHeaderClaim('Token', 'FirstAuthAPI')
                ->setSecret('53f1d8af82283491b2fe98310ccf9a75nE$!')
                ->setIssuer('API Authenticator')
                ->setSubject('api-auth-token')
                ->setAudience('https://yourapi.com')
                ->setExpiration($exp)
                ->setIssuedAt(time())
                ->setJwtId(md5(uniqid('TOKEN')))
                ->setPayloadClaim('user', $userInfos)
                ->build();
        } catch (ValidateException $e) {
            die("Build error...");
        }
        $this->value = $t->getToken();
        $this->user = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getCreation(): ?DateTimeInterface
    {
        return $this->creation;
    }

    public function setCreation(DateTimeInterface $creation): self
    {
        $this->creation = $creation;

        return $this;
    }

    public function getExpiration(): ?DateTimeInterface
    {
        return $this->expiration;
    }

    public function setExpiration(DateTimeInterface $expiration): self
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @param $secret
     * @return bool
     */
    public function isValid($secret)
    {
        $expireDate = strtotime(($this->getExpiration())->format('Y-m-d'));
        $tokenExpireDate = Tokenizer::getPayload($this->getValue(), $secret)['exp'];
        if ($expireDate !== $tokenExpireDate)
            throw new Exception('Invalid or modified token...', 000004);
        return true;

    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     */
    public function setAccessToken(?AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }


}
