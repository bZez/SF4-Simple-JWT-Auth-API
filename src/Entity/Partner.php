<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PartnerRepository")
 */
class Partner
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="partner", orphanRemoval=true)
     */
    private $users;

    /**
     * @ORM\Column(type="json")
     */
    private $privileges = [];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User")
     */
    private $admin;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccessRequest", mappedBy="partner", orphanRemoval=true)
     */
    private $accessRequests;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->accessRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setPartner($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getPartner() === $this) {
                $user->setPartner(null);
            }
        }

        return $this;
    }

    public function getPrivileges(): ?array
    {
        return $this->privileges;
    }

    public function addPrivilege($controller, $methods, $actions)
    {
        $prArr = $this->getPrivileges();
        foreach ($methods as $method) {
            $prArr[$method][$controller] = [];
            foreach ($actions as $action) {
                $prArr[$method][$controller][] .= $action;
            }
        }
        $this->setPrivileges($prArr);
    }

    public function removePrivilegeAction($controller, $method, $action)
    {
        $prArr = $this->getPrivileges();
        if (($key = array_search($action, $prArr[$method][$controller])) !== false) {
            unset($prArr[$method][$controller][$key]);
        }
        $this->setPrivileges($prArr);
    }

    public function removePrivilegeMethod($controller, $method)
    {
        $prArr = $this->getPrivileges();
        unset($prArr[$method][$controller]);
        $this->setPrivileges($prArr);
    }

    public function removePrivilege($controller)
    {
        $prArr = $this->getPrivileges();
        unset($prArr['GET'][$controller]);
        unset($prArr['POST'][$controller]);
        unset($prArr['PUT'][$controller]);
        unset($prArr['DELETE'][$controller]);
        $this->setPrivileges($prArr);
    }


    public function setPrivileges(array $privileges): self
    {
        $this->privileges = $privileges;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param mixed $admin
     */
    public function setAdmin($admin): void
    {
        $this->admin = $admin;
    }

    /**
     * @return Collection|AccessRequest[]
     */
    public function getAccessRequests(): Collection
    {
        return $this->accessRequests;
    }

    public function addAccessRequest(AccessRequest $accessRequest): self
    {
        if (!$this->accessRequests->contains($accessRequest)) {
            $this->accessRequests[] = $accessRequest;
            $accessRequest->setPartner($this);
        }

        return $this;
    }

    public function removeAccessRequest(AccessRequest $accessRequest): self
    {
        if ($this->accessRequests->contains($accessRequest)) {
            $this->accessRequests->removeElement($accessRequest);
            // set the owning side to null (unless already changed)
            if ($accessRequest->getPartner() === $this) {
                $accessRequest->setPartner(null);
            }
        }

        return $this;
    }

}
