<?php

namespace App\Entity;

use DateTime;
use ReallySimpleJWT\Build;
use ReallySimpleJWT\Encode;
use ReallySimpleJWT\Exception\ValidateException;
use ReallySimpleJWT\Token as Tokenizer;
use ReallySimpleJWT\Validate;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccessTokenRepository")
 */
class AccessToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $creation;

    /**
     * @ORM\Column(type="date")
     */
    private $expiration;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\authToken")
     * @ORM\JoinColumn(nullable=false)
     */
    private $authToken;

    /**
     * @ORM\Column(type="string", length=4096)
     */
    private $value;

    public function __construct(AuthToken $auth)
    {
        $tokenBuilder = new Build('JWT', new Validate(), new Encode());
        $this->creation = new DateTime();
        $this->expiration = $this->creation->modify("+1year");
        $exp = strtotime($this->expiration->format('Y-m-d'));
        try {
            $t = $tokenBuilder->setContentType('JWT')
                ->setHeaderClaim('Token', 'ApiAccessToken')
                ->setSecret('53f1d8af82283491b2fe98310ccf9a75nE$!')
                ->setIssuer('API Access Generator')
                ->setSubject('api-access-token')
                ->setAudience('https://yourapi.com')
                ->setExpiration($exp)
                ->setIssuedAt(time())
                ->setJwtId(md5(uniqid('TOKEN')))
                ->setPayloadClaim('privileges', $auth->getUser()->getPrivileges())
                ->build();
        } catch (ValidateException $e) {
            die("Build error...");
        }
        $this->value = $t->getToken();
        $this->authToken = $auth;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * @param mixed $creation
     */
    public function setCreation($creation): void
    {
        $this->creation = $creation;
    }

    public function getAuthToken(): ?authToken
    {
        return $this->authToken;
    }

    public function setAuthToken(?authToken $authToken): self
    {
        $this->authToken = $authToken;

        return $this;
    }

    public function isValid($secret)
    {
        $expireDate = strtotime(($this->getExpiration())->format('Y-m-d'));
        $tokenExpireDate = Tokenizer::getPayload($this->getValue(), $secret)['exp'];
        if ($expireDate !== $tokenExpireDate)
            throw new Exception('Invalid or modified token...', 000004);

    }

    /**
     * @return mixed
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param mixed $expiration
     */
    public function setExpiration($expiration): void
    {
        $this->expiration = $expiration;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

}
