<?php

namespace Vivait\AuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Tenant
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vivait\AuthBundle\Entity\TenantRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @UniqueEntity("code")
 */
class Tenant {
	/**
	 * @var integer
	 * @ORM\Column(name="id", type="guid")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
     * @Serializer\Groups({"basic"})
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="tenant", type="string", length=255)
	 * @Assert\Type(type="string")
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "3", max="255");
     * @Serializer\Groups({"basic"})
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
     * @Serializer\Groups({"basic"})
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
		 * @ORM\ManyToMany(targetEntity="Vivait\AuthBundle\Entity\User", mappedBy="tenants")
		 */
		private $users;

	/**
	 * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
	 */
	private $deletedAt;
		/**
		 * @var Group[]|ArrayCollection
		 * @ORM\ManyToMany(targetEntity="Vivait\AuthBundle\Entity\Group", inversedBy="tenants")
		 */
		private $groups;

	/**
	 * Get id
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set tenant
	 *
	 * @param string $tenant
	 *
	 * @return Tenant
	 */
	public function setTenant( $tenant ) {
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
	 *
	 * @param string $code
	 *
	 * @return Tenant
	 */
	public function setCode( $code ) {
		$this->code = strtoupper( $code );

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
	public function __construct( $code = null, $tenant = null, $licensed_until = null ) {
		$this->users          = new ArrayCollection();
			$this->groups    = new ArrayCollection();
		$this->priority       = 100;
		$this->active         = 1;
		$this->code           = $code;
		$this->tenant         = $tenant;
		$this->licenseduntil  = $licensed_until ?: new \DateTime('+1 month');
	}

	/**
	 * Add users
	 *
	 * @param User $users
	 *
	 * @return Tenant
	 */
	public function addUser( User $users ) {
		$this->users[] = $users;
		$users->addTenant( $this );

		return $this;
	}

	/**
	 * Remove users
	 *
	 * @param User $users
	 */
	public function removeUser( User $users ) {
		$this->users->removeElement( $users );
		$users->removeTenant( $this );
	}

	/**
	 * Get users
	 * @return ArrayCollection|User[]
	 */
	public function getUsers() {
		return $this->users;
	}

	public function getDeletedAt() {
		return $this->deletedAt;
	}

	public function setDeletedAt( $deletedAt ) {
		$this->deletedAt = $deletedAt;
	}

	/**
	 * Set priority
	 *
	 * @param integer $priority
	 *
	 * @return Tenant
	 */
	public function setPriority( $priority ) {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Get priority
	 *
	 * @return integer
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * Set active
	 *
	 * @param boolean $active
	 *
	 * @return Tenant
	 */
	public function setActive( $active ) {
		$this->active = $active;

		return $this;
	}

	/**
	 * Get active
	 *
	 * @return boolean
	 */
	public function getActive() {
		return $this->active;
	}

	/**
	 * Set licenseduntil
	 *
	 * @param \DateTime $licenseduntil
	 *
	 * @return Tenant
	 */
	public function setLicenseduntil( $licenseduntil ) {
		$this->licenseduntil = $licenseduntil;

		return $this;
	}

	/**
	 * Get licenseduntil
	 *
	 * @return \DateTime
	 */
	public function getLicenseduntil() {
		return $this->licenseduntil;
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
		 * (PHP 5 &gt;= 5.4.0)<br/>
		 * Specify data which should be serialized to JSON
		 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
		 * @return mixed data which can be serialized by <b>json_encode</b>,
		 * which is a value of any type other than a resource.
		 */
		public function jsonSerialize() {
			return [
				'id'       => $this->id,
				'code'     => $this->code,
				'tenant'   => $this->tenant,
				'location' => $this->location
			];
		}


      public function __toString()
      {
          return $this->getTenant();
      }
}
