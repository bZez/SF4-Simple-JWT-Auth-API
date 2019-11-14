<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(type="string", length=4096)
     */
    private $value;

    /**
     * @ORM\Column(type="string",length=255)
     */
    private $controller;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\AuthToken", mappedBy="AccessTokens")
     */
    private $authTokens;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ip;

    public function __construct(AuthToken $auth,$controller)
    {
        $tokenBuilder = new Build('JWT', new Validate(), new Encode());
        $this->creation = new DateTime();
        $this->controller = $controller;
        $this->expiration = $this->creation->modify("+1year");
        try {
            $t = $tokenBuilder->setContentType('JWT')
                ->setHeaderClaim('partner', $auth->getUser()->getPartner()->getId())
                ->setSecret('53f1d8af82283491b2fe98310ccf9a75nE$!')
                ->setIssuer('API Access Generator')
                ->setJwtId(md5(uniqid('TOKEN')))
                ->setPayloadClaim('controller', $controller)
                ->build();
        } catch (ValidateException $e) {
            die("Build error...");
        }
        $this->value = $t->getToken();
        $this->authTokens = new ArrayCollection();
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


    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param mixed $controller
     */
    public function setController($controller): void
    {
        $this->controller = $controller;
    }

    /**
     * @return Collection|AuthToken[]
     */
    public function getAuthTokens(): Collection
    {
        return $this->authTokens;
    }

    public function addAuthToken(AuthToken $authToken): self
    {
        if (!$this->authTokens->contains($authToken)) {
            $this->authTokens[] = $authToken;
            $authToken->addAccessToken($this);
        }

        return $this;
    }

    public function removeAuthToken(AuthToken $authToken): self
    {
        if ($this->authTokens->contains($authToken)) {
            $this->authTokens->removeElement($authToken);
            $authToken->removeAccessToken($this);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip): void
    {
        $this->ip = $ip;
    }

}
