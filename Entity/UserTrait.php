<?php

namespace BisonLab\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use BisonLab\UserBundle\Lib\ExternalEntityConfig;
use BisonLab\UserBundle\Entity\Group;

trait UserTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var datetime Last Login
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_login;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $last_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $full_name;

    public function __construct()
    {
        $this->last_login = new \DateTime();
        $this->groups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLastLogin(): \Datetime
    {
        return $this->last_login;
    }

    public function setLastLogin(\DateTime $last_login): self
    {
        $this->last_login = $last_login;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        /*
         * Bringing this from FOS User and have used it.
         * But not ideal when it comes to filtering it out in setRoles..
         */
        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        /*
         * The common routine here is to add ROLE_USER, but that defies the
         * reason I am making this bundle.
         * Alas, I pick the default role from the config.
         */
        $roles[] = ExternalEntityConfig::getDefaultRole();

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        foreach ($roles as $role) {
            if (!in_array($role, ExternalEntityConfig::getRoles()))
                throw new \InvalidArgumentException(sprintf('The "%s" role is not a valid role.', $role));
        }
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): self
    {
        $this->first_name = $first_name;

        $this->_setFullName();
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): self
    {
        $this->last_name = $last_name;
        $this->_setFullName();

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    private function _setFullName(): self
    {
        $this->full_name = implode(" ", [$this->first_name, $this->last_name]);

        return $this;
    }

    /**
     * @return Collection|Group[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }

    public function isAdmin(): bool
    {
        foreach (ExternalEntityConfig::getRolesConfig() as $role => $conf) {
            if (!$conf['admin'])
                continue;
            if (in_array($role ,$this->getRoles()))
                return true;
        }
        return false;
    }

    public function isSuperAdmin(): bool
    {
        foreach (ExternalEntityConfig::getRolesConfig() as $role => $conf) {
            if (!$conf['superadmin'])
                continue;
            if (in_array($role ,$this->getRoles()))
                return true;
        }
        return false;
    }

    public function isEnabled(): bool
    {
        return !$this->isDisabled();
    }

    /*
     * Check if any roles has enabled false.
     * Which means any not enabled role makes this return true.
     */
    public function isDisabled(): bool
    {
        foreach (ExternalEntityConfig::getRolesConfig() as $role => $conf) {
            if ($conf['enabled'])
                continue;
            if (in_array($role ,$this->getRoles()))
                return true;
        }
        return false;
    }

    public function hasGroup($name): bool
    {
        return in_array($name, $this->getGroupNames());
    }

    public function hasRole($role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function getGroupNames()
    {
        $names = array();
        foreach ($this->getGroups() as $g) {
            $names[] = $g->getName();
        }
        return $names;
    }

    public function getRoleLabels()
    {
        $labels = array();
        $rconf = ExternalEntityConfig::getRolesConfig();
        foreach ($this->getRoles() as $role) {
            if (isset($rconf[$role]))
                $labels[] = $rconf[$role]['label'];
        }
        return $labels;
    }

    public function __toString(): string
    {
        return $this->full_name ?: $this->getUserName();
    }
}
