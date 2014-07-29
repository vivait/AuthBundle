<?php

namespace Vivait\AuthBundle\Entity;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Vivait\Common\Model\Footprint\UserInterface as FootprintUserInterface;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 * @ORM\MappedSuperclass()
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
abstract class BaseUser implements AdvancedUserInterface, \Serializable, \JsonSerializable, FootprintUserInterface {
    const STATUS_UNKNOWN = 0;
    const STATUS_ONLINE  = 10;
    const STATUS_AWAY    = 11;
    const DEFAULT_GRAVATAR = 'wavatar';

    public static function getAllStatus() {
        $a = array(
            self::STATUS_ONLINE => 'Online',
            self::STATUS_AWAY   => 'Away',
        );
        return $a;
    }

    public function getStatusName() {
        foreach (self::getAllStatus() as $key => $value) {
            if ($key == $this->status) {
                return $value;
            }
        }
        return 'Unknown';
    }

    public abstract  function jsonSerialize();

    public abstract function __toString();

    /**
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Serializer\Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\Type(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(min = "3", max="25");
     * @Serializer\Groups({"basic"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=88)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\Type(type="string")
     * @Assert\NotBlank()
     * @Assert\Email
     * @Assert\Length(min = "3", max="60");
     * @Serializer\Groups({"basic"})
     */
    private $email;

    /**
     * @var Group[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="Vivait\AuthBundle\Entity\Group", inversedBy="users")
     */
    private $groups;

    /**
     * @ORM\ManyToMany(targetEntity="Vivait\AuthBundle\Entity\Tenant", inversedBy="users")
     * @ORM\OrderBy({"priority" = "ASC", "tenant" = "ASC"})
     */
    private $tenants;

    /**
     * @var Tenant
     * @ORM\ManyToOne(targetEntity="Vivait\AuthBundle\Entity\Tenant")
     * @ORM\JoinColumn(name="current_tenant", referencedColumnName="id")
     **/
    private $current_tenant;

    /**
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     * @ORM\Column(name="lastactivity", type="datetime", nullable=true)
     */
    private $lastactivity;

    /**
     * @var \DateTime
     * @ORM\Column(name="lastresponse", type="datetime", nullable=true)
     */
    private $lastresponse;

    /**
     * @var string
     * @ORM\Column(name="lastip", type="string", length=46, nullable=true)
     */
    private $lastip;

    /**
     * @var string
     * @ORM\Column(name="lasturl", type="string", length=255, nullable=true)
     */
    private $lasturl;

    /**
     * @var string
     * @ORM\Column(name="lastua", type="string", length=255, nullable=true)
     */
    private $lastua;

    /**
     * @var integer
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $tfkey;

    private $gravatarhash;

    /**
     * This is called once Doctrine has loaded the entity
     * @ORM\PostLoad
     */

#############################################

    public function __construct() {
        $this->newSalt();
        $this->active       = true;
        $this->groups       = new ArrayCollection();
        $this->tenants      = new ArrayCollection();
    }

    public function isAccountNonExpired() {
        return $this->isTenantLicensed();
    }

    public function isAccountNonLocked() {
        return true;
    }

    public function isCredentialsNonExpired() {
        return true;
    }

    public function isEnabled() {
        return $this->active && $this->isTenantActive();
    }


    protected function isTenantActive() {
        foreach ($this->getTenants() as $tenant) {
            if ($tenant->getActive()) {
                return true;
            }
        }
        return false;
    }

    protected function isTenantLicensed() {
        foreach ($this->getTenants() as $tenant) {
            if ($tenant->getLicenseduntil() > new \DateTime()) {
                return true;
            }
        }
        return false;
    }

    public function getLicensedUntil() {
        $licensed_until = new \DateTime('2000-01-01');
        foreach ($this->getTenants() as $tenant) {
            if ($tenant->getLicenseduntil() > $licensed_until) {
                $licensed_until = $tenant->getLicenseduntil();
            }
        }
        return $licensed_until;
    }

    /**
     * @inheritDoc
     */
    public function getUsername() {
        return $this->username;
    }

    public function newSalt() {
        $this->salt = md5(uniqid(null, true));
    }

    /**
     * @inheritDoc
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function getRoles() {
        $roles = array();
        foreach ($this->groups as $role) {
            $roles[] = $role->getRole();
        }

        if($this->getCurrentTenant()) {
            foreach($this->getCurrentTenant()->getGroups() as $role) {
                $roles[] = $role->getRole();
            }
        }
        return $roles;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials() {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize() {
        return serialize(array(
                $this->id,
                $this->active,
                $this->username
            ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized) {
        list (
            $this->id,
            $this->active,
            $this->username
            ) = unserialize($serialized);
    }

    /**
     * Get id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set username
     * @param string $username
     * @return User
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }


    /**
     * Set salt
     * @param string $salt
     * @return User
     */
    public function setSalt($salt) {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set password
     * @param string $password
     * @return User
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Set email
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set active
     * @param boolean $active
     * @return User
     */
    public function setActive($active) {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     * @return boolean
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * Add groups
     * @param Group $groups
     * @return User
     */
    public function addGroup(Group $groups) {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     * @param Group $groups
     */
    public function removeGroup(Group $groups) {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     * @return Group[]|ArrayCollection
     */
    public function getGroups() {
        return $this->groups;
    }


    /**
     * Add tenants
     * @param Tenant $tenants
     * @return User
     */
    public function addTenant(Tenant $tenants) {
        $this->tenants[] = $tenants;

        return $this;
    }

    /**
     * Remove tenants
     * @param Tenant $tenants
     */
    public function removeTenant(Tenant $tenants) {
        $this->tenants->removeElement($tenants);
    }

    /**
     * Get tenants
     * @return Tenant[]|ArrayCollection
     */
    public function getTenants() {
        return $this->tenants;
    }

    /**
     * Sets current_tenant
     * @param Tenant $current_tenant
     * @return $this
     */
    public function setCurrentTenant(Tenant $current_tenant) {
        $this->current_tenant = $current_tenant;
        return $this;
    }

    /**
     * @return Tenant
     */
    public function getCurrentTenant() {
        return $this->current_tenant;
    }

    public function getAllowedTenants() {
        $current_tenant = $this->getCurrentTenant();

        // Restrict them to just the current tenant
        if ($current_tenant) {
            return array($current_tenant->getId());
        }

        $tenants = array();

        foreach ($this->getTenants() as $tenant) {
            $tenants[] = $tenant->getId();
        }

        return $tenants;
    }

    /**
     * Set lastactivity
     * @param \DateTime $lastactivity
     * @return User
     */
    public function setLastactivity($lastactivity) {
        $this->lastactivity = $lastactivity;

        return $this;
    }

    /**
     * Get lastactivity
     * @return \DateTime
     */
    public function getLastactivity() {
        return $this->lastactivity;
    }

    /**
     * Set status
     * @param integer $status
     * @return User
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     * @return integer
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set lastresponse
     * @param \DateTime $lastresponse
     * @return User
     */
    public function setLastresponse($lastresponse) {
        $this->lastresponse = $lastresponse;

        return $this;
    }

    /**
     * Get lastresponse
     * @return \DateTime
     */
    public function getLastresponse() {
        return $this->lastresponse;
    }

    /**
     * Set Lastip
     * @param string $lastip
     * @return $this
     */
    public function setLastip($lastip) {
        $this->lastip = $lastip;
        return $this;
    }

    /**
     * Get Lastip
     * @return string
     */
    public function getLastip() {
        return $this->lastip;
    }

    /**
     * Set tfkey
     * @param string $tfkey
     * @return User
     */
    public function setTfkey($tfkey) {
        $this->tfkey = $tfkey;

        return $this;
    }

    /**
     * Get tfkey
     * @return string
     */
    public function getTfkey() {
        return $this->tfkey;
    }

    public function getGravatarHash() {
        if (!$this->gravatarhash) {
            $this->gravatarhash = md5(strtolower(trim($this->email)));
        }

        return $this->gravatarhash;
    }

    public function getGravatar() {
        return sprintf('//www.gravatar.com/avatar/%s?d=%s',$this->getGravatarHash(), self::DEFAULT_GRAVATAR);
    }
    /**
     * Get Lastua
     * @return string
     */
    public function getLastua() {
        return $this->lastua;
    }
    /**
     * Set Lastua
     * @param string $lastua
     * @return $this
     */
    public function setLastua($lastua) {
        $this->lastua = $lastua;
        return $this;
    }
    /**
     * Get Lasturl
     * @return string
     */
    public function getLasturl() {
        return $this->lasturl;
    }
    /**
     * Set Lasturl
     * @param string $lasturl
     * @return $this
     */
    public function setLasturl($lasturl) {
        $this->lasturl = $lasturl;
        return $this;
    }

    public function hashPassword(EncoderFactoryInterface $encoder_factory){
        $this->newSalt();
        $encoder  = $encoder_factory->getEncoder( $this );
        $password = $encoder->encodePassword( $this->getPassword(), $this->getSalt() );

        $this->setPassword( $password );
    }
}
