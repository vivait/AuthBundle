<?php

namespace Viva\AuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use \Viva\SettingsBundle\Entity\Settings;
use \Viva\BravoBundle\Entity\Queue;


/**
 * Tenant
 * @ORM\Table()
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @UniqueEntity("code")
 */
class Tenant {
	/**
	 * @var integer
	 * @ORM\Column(name="id", type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="tenant", type="string", length=255)
	 * @Assert\Type(type="string")
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "3", max="64");
	 */
	private $tenant;

	/**
	 * @var integer
	 * @ORM\Column(name="priority", type="integer")
	 * @Assert\Type(type="integer")
	 */
	private $priority;

	/**
	 * @var string
	 * @ORM\Column(name="code", type="string", length=64, unique=true)
	 * @Assert\Type(type="string")
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "3", max="64");
	 */
	private $code;

	/**
	 * @ORM\Column(name="active", type="boolean")
	 */
	private $active;

	/**
	 * @ORM\Column(name="licenseduntil", type="datetime")
	 */
	private $licenseduntil;

	/**
	 * @ORM\ManyToMany(targetEntity="User", mappedBy="tenants")
	 */
	private $users;

	/**
	 * @ORM\OneToMany(targetEntity="\Viva\SettingsBundle\Entity\Settings", mappedBy="tenant")
	 */
	private $settings;

	/**
	 * @ORM\ManyToMany(targetEntity="\Viva\BravoBundle\Entity\Queue", mappedBy="tenants")
	 */
	private $queues;

	/**
	 * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
	 */
	private $deletedAt;

	/**
	 * Get id
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set tenant
	 * @param string $tenant
	 * @return Tenant
	 */
	public function setTenant($tenant) {
		$this->tenant = $tenant;

		return $this;
	}

	/**
	 * Get tenant
	 * @return string
	 */
	public function getTenant() {
		return $this->tenant;
	}

	/**
	 * Set code
	 * @param string $code
	 * @return Tenant
	 */
	public function setCode($code) {
		$this->code = strtoupper($code);

		return $this;
	}

	/**
	 * Get code
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = new ArrayCollection();
		$this->queues   = new ArrayCollection();
		$this->users    = new ArrayCollection();
		$this->priority = 100;
		$this->active   = 1;
	}

	/**
	 * Add users
	 * @param User $users
	 * @return Tenant
	 */
	public function addUser(User $users) {
		$this->users[] = $users;
		$users->addTenant($this);

		return $this;
	}

	/**
	 * Remove users
	 * @param User $users
	 */
	public function removeUser(User $users) {
		$this->users->removeElement($users);
		$users->removeTenant($this);
	}

	/**
	 * Get users
	 * @return ArrayCollection|User[]
	 */
	public function getUsers() {
		return $this->users;
	}

	/**
	 * Add settings
	 *
	 * @param Settings $settings
	 * @return Tenant
	 */
	public function addSetting(Settings $settings) {
		$this->settings[] = $settings;
		$settings->setTenant($this);

		return $this;
	}

	/**
	 * Remove settings
	 *
	 * @param Settings $settings
	 */
	public function removeSetting(Settings $settings) {
		$this->settings->removeElement($settings);
	}

	/**
	 * Get settings
	 *
	 * @return ArrayCollection|Settings[]
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * Add queues
	 *
	 * @param Queue $queues
	 * @return Tenant
	 */
	public function addQueue(Queue $queues) {
		$this->queues[] = $queues;

		return $this;
	}

	/**
	 * Remove queues
	 *
	 * @param Queue $queues
	 */
	public function removeQueue(Queue $queues) {
		$this->queues->removeElement($queues);
	}

	/**
	 * Get queues
	 *
	 * @return ArrayCollection|Queue[]
	 */
	public function getQueues() {
		return $this->queues;
	}

	public function getDeletedAt()
	{
		return $this->deletedAt;
	}

	public function setDeletedAt($deletedAt)
	{
		$this->deletedAt = $deletedAt;
	}

    /**
     * Set priority
     *
     * @param integer $priority
     * @return Tenant
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    
        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Tenant
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set licenseduntil
     *
     * @param \DateTime $licenseduntil
     * @return Tenant
     */
    public function setLicenseduntil($licenseduntil)
    {
        $this->licenseduntil = $licenseduntil;
    
        return $this;
    }

    /**
     * Get licenseduntil
     *
     * @return \DateTime 
     */
    public function getLicenseduntil()
    {
        return $this->licenseduntil;
    }
}