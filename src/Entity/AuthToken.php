<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
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
     * @ORM\ManyToMany(targetEntity="App\Entity\AccessToken", inversedBy="authTokens")
     */
    private $AccessTokens;


    public function __construct($user)
    {
        $tokenBuilder = new Build('JWT', new Validate(), new Encode());
        $this->creation = new DateTime();
        $this->expiration = $this->creation->modify("+1year");
        $userInfos = [
            "login" => $user->getEmail(),
            "roles" => $user->getRoles(),
            "partner" => $user->getPartner()->getName(),
        ];
        try {
            $t = $tokenBuilder->setContentType('JWT')
                ->setHeaderClaim('for', strtoupper($user->getLastName()) . ' ' . ucfirst($user->getFirstName()))
                ->setSecret('kB=&ah6M@VtK^yQbf&P9xDrkvcQh_emm55y3Kq#jy=DxLy$MufnPG6vuW33Z?v$')
                ->setIssuer('API Authenticator')
                ->setJwtId(md5(uniqid('TOKEN')))
                ->setPayloadClaim('user', $userInfos)
                ->build();
        } catch (ValidateException $e) {
            die("Build error...");
        }
        $this->value = $t->getToken();
        $this->user = $user;
        $this->AccessTokens = new ArrayCollection();
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

    public function getCreation(): ?DateTimeInterface
    {
        return $this->creation;
    }

    public function setCreation(DateTimeInterface $creation): self
    {
        $this->creation = $creation;

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
     * @throws Exception
     * @return bool
     */
    public function isValid($secret)
    {
        if (!Tokenizer::validate($this->getValue(), $secret))
            throw new Exception('Invalid or modified token...', 000004);
        return true;
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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Collection|AccessToken[]
     */
    public function getAccessTokens(): Collection
    {
        return $this->AccessTokens;
    }

    public function addAccessToken(AccessToken $accessToken): self
    {
        if (!$this->AccessTokens->contains($accessToken)) {
            $this->AccessTokens[] = $accessToken;
        }

        return $this;
    }

    public function removeAccessToken(AccessToken $accessToken): self
    {
        if ($this->AccessTokens->contains($accessToken)) {
            $this->AccessTokens->removeElement($accessToken);
        }

        return $this;
    }

}
