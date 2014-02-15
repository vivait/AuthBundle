<?php

	namespace Vivait\AuthBundle\Entity;

	use Symfony\Component\Security\Core\User\AdvancedUserInterface;
	use Doctrine\ORM\Mapping as ORM;
	use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
	use Symfony\Component\Validator\Constraints as Assert;
	use Doctrine\Common\Collections\ArrayCollection;

#custom use
	use Viva\BravoBundle\Entity\Deal;
	use Viva\BravoBundle\Entity\Offer;
	use Viva\BravoBundle\Entity\Queue;
	use Viva\BravoBundle\Entity\Action;
	use Viva\BravoBundle\Entity\History;
	use Viva\BravoBundle\Entity\Customer;
###


	/**
	 * @ORM\Entity
	 * @UniqueEntity("username")
	 * @UniqueEntity("email")
	 * @ORM\Table(name="Users")
	 * @ORM\Entity(repositoryClass="Vivait\AuthBundle\Entity\UserRepository")
	 */
	class User implements AdvancedUserInterface, \Serializable, \JsonSerializable {

		const STATUS_UNKNOWN = 0;
		const STATUS_ONLINE  = 10;
		const STATUS_AWAY    = 11;

		public static function getAllStatus() {
			$a = array(
				self::STATUS_ONLINE => 'Online',
				self::STATUS_AWAY   => 'Away',
			);
			return $a;
		}

		public function getStatusName() {
			foreach(self::getAllStatus() as $key => $value) {
				if($key == $this->status) {
					return $value;
				}
			}
			return 'Unknown';
		}

		public function jsonSerialize() {
			return array(
				'id'       => $this->id,
				'initials' => $this->initials,
				'fullname' => $this->fullname,
				'username' => $this->username,
				//			'gravatar' => $this->getGravatar(),
			);
		}

		public function __toString() {
			return $this->fullname;
		}

		/**
		 * @ORM\Column(type="integer")
		 * @ORM\Id
		 * @ORM\GeneratedValue(strategy="AUTO")
		 */
		private $id;

		/**
		 * @ORM\Column(type="string", length=25, unique=true)
		 * @Assert\Type(type="string")
		 * @Assert\NotBlank()
		 * @Assert\Length(min = "3", max="25");
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
		 */
		private $email;

		/**
		 * @ORM\Column(type="string", length=60)
		 * @Assert\NotBlank()
		 * @Assert\Length(min = "3", max="60");
		 * @Assert\Type(type="string")
		 */
		private $fullname;

		/**
		 * @ORM\Column(type="string", length=10, nullable=true)
		 * @Assert\NotBlank()
		 * @Assert\Length(min = "2", max="10");
		 * @Assert\Type(type="string")
		 */
		private $initials;

		/**
		 * @ORM\Column(type="string", length=60, nullable=true)
		 * @Assert\Length(max="60");
		 * @Assert\Type(type="string")
		 */
		private $jobtitle;

		/**
		 * @ORM\Column(type="string", length=60, nullable=true)
		 * @Assert\Length(max="60");
		 * @Assert\Type(type="string")
		 */
		private $department;

		/**
		 * @ORM\Column(type="string", length=60, nullable=true)
		 * @Assert\Length(max="60");
		 * @Assert\Type(type="string")
		 */
		private $location;

		/**
		 * @ORM\Column(type="string", length=15, nullable=true)
		 * @Assert\Regex("^\+?[0-9]{11,15}*$")
		 */
		private $telephone;

		/**
		 * @var Group[]
		 * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
		 */
		private $groups;

		/**
		 * @ORM\ManyToMany(targetEntity="Tenant", inversedBy="users")
		 * @ORM\OrderBy({"priority" = "ASC", "tenant" = "ASC"})
		 */
		private $tenants;

		/* @var $current_tenant Tenant */
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
		 * @var integer
		 * @ORM\Column(name="status", type="integer", nullable=true)
		 */
		private $status;

		/**
		 * @ORM\Column(type="string", length=10, nullable=true)
		 */
		private $tfkey;

//	private $gravatarhash;

		/**
		 * Show the application status instead of Queue 4 on the home page
		 * @var boolean
		 * @ORM\Column(name="appstatus", type="boolean", nullable=true)
		 */
		private $appstatus;


		/**
		 * This is called once Doctrine has loaded the entity
		 * @ORM\PostLoad
		 */

		##############CUSTOM FIELDS#####################
		/**
		 * @var = Deal[]
		 * @ORM\ManyToMany(targetEntity="Viva\BravoBundle\Entity\Deal", mappedBy="user", cascade={"persist", "remove"})
		 */
		private $deals;

		/**
		 * @var History[]
		 * @ORM\OneToMany(targetEntity="Viva\BravoBundle\Entity\History", mappedBy="user", cascade={"persist", "remove"})
		 */
		private $history;

		/**
		 * @var Action[]
		 * @ORM\OneToMany(targetEntity="Viva\BravoBundle\Entity\Action", mappedBy="user", cascade={"persist"})
		 * @ORM\OrderBy({"when" = "DESC"})
		 */
		private $action;

		/**
		 * @var Queue[]
		 * @ORM\ManyToMany(targetEntity="Viva\BravoBundle\Entity\Queue", mappedBy="users1")
		 * @ORM\OrderBy({"priority" = "DESC"})
		 */
		protected $queues1;

		/**
		 * @var Queue[]
		 * @ORM\ManyToMany(targetEntity="Viva\BravoBundle\Entity\Queue", mappedBy="users2")
		 * @ORM\OrderBy({"priority" = "DESC"})
		 */
		protected $queues2;

		/**
		 * @var Queue[]
		 * @ORM\ManyToMany(targetEntity="Viva\BravoBundle\Entity\Queue", mappedBy="users3")
		 * @ORM\OrderBy({"priority" = "DESC"})
		 */
		protected $queues3;

		/**
		 * @var Queue[]
		 * @ORM\ManyToMany(targetEntity="Viva\BravoBundle\Entity\Queue", mappedBy="users4")
		 * @ORM\OrderBy({"priority" = "DESC"})
		 */
		protected $queues4;

		/**
		 * @var Queue[]
		 * @ORM\ManyToMany(targetEntity="Viva\BravoBundle\Entity\Queue", mappedBy="sendtousers")
		 * @ORM\OrderBy({"name" = "ASC"})
		 */
		protected $sendtoqueues;

		/**
		 * @var Action
		 * @ORM\OneToMany(targetEntity="Viva\BravoBundle\Entity\Offer", mappedBy="user")
		 */
		private $offer;

#############################################

		public function __construct() {
			$this->salt         = md5(uniqid(null, true));
			$this->active       = true;
			$this->deals        = new ArrayCollection();
			$this->groups       = new ArrayCollection();
			$this->history      = new ArrayCollection();
			$this->action       = new ArrayCollection();
			$this->tenants      = new ArrayCollection();
			$this->sendtoqueues = new ArrayCollection();
			$this->queues1      = new ArrayCollection();
			$this->queues2      = new ArrayCollection();
			$this->queues3      = new ArrayCollection();
			$this->queues4      = new ArrayCollection();
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


		private function isTenantActive() {
			foreach ($this->getTenants() as $tenant) {
				if($tenant->getActive()) {
					return true;
				}
			}
			return false;
		}

		private function isTenantLicensed() {
			foreach ($this->getTenants() as $tenant) {
				if($tenant->getLicenseduntil() > new \DateTime()) {
					return true;
				}
			}
			return false;
		}

		public function getLicensedUntil() {
			$licensed_until = new \DateTime('2000-01-01');
			foreach ($this->getTenants() as $tenant) {
				if($tenant->getLicenseduntil() > $licensed_until) {
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
		 * Set fullname
		 * @param string $fullname
		 * @return User
		 */
		public function setFullname($fullname) {
			$this->fullname = $fullname;

			return $this;
		}

		/**
		 * A helper method to get the tenanted full name
		 * @return string
		 */
		public function getTenantedFullname() {
			return $this->getFullname(true);
		}

		/**
		 * Get fullname
		 * @param $include_tenant boolean
		 * @return string
		 */
		public function getFullname($include_tenant = false) {
			$tenant = '';

			if($include_tenant && ($tenants = $this->getTenants()) && $tenants->count()) {

				foreach($tenants as $row) {
					$tenant .= $row->getCode() . ', ';
				}

				$tenant = '(' . substr($tenant, 0, -2) . ') ';
			}

			return $tenant . $this->fullname;
		}


		public function getForename() {
			$names = explode(' ', $this->fullname);
			return $names[0];
		}

		/**
		 * Set initials
		 * @param string $initials
		 * @return User
		 */
		public function setInitials($initials) {
			$this->initials = $initials;

			return $this;
		}

		/**
		 * Get initials
		 * @return string
		 */
		public function getInitials() {
			return $this->initials;
		}

		/**
		 * Set jobtitle
		 * @param string $jobtitle
		 * @return User
		 */
		public function setJobtitle($jobtitle) {
			$this->jobtitle = $jobtitle;

			return $this;
		}

		/**
		 * Get jobtitle
		 * @return string
		 */
		public function getJobtitle() {
			return $this->jobtitle;
		}

		/**
		 * Set department
		 * @param string $department
		 * @return User
		 */
		public function setDepartment($department) {
			$this->department = $department;

			return $this;
		}

		/**
		 * Get department
		 * @return string
		 */
		public function getDepartment() {
			return $this->department;
		}

		/**
		 * Set location
		 * @param string $location
		 * @return User
		 */
		public function setLocation($location) {
			$this->location = $location;

			return $this;
		}

		/**
		 * Get location
		 * @return string
		 */
		public function getLocation() {
			return $this->location;
		}

		/**
		 * Set telephone
		 * @param string $telephone
		 * @return User
		 */
		public function setTelephone($telephone) {
			$this->telephone = $telephone;

			return $this;
		}

		/**
		 * Get telephone
		 * @return string
		 */
		public function getTelephone() {
			return $this->telephone;
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
			if($current_tenant) {
				return array($current_tenant->getId());
			}

			$tenants = array();

			foreach($this->getTenants() as $tenant) {
				$tenants[] = $tenant->getId();
			}

			return $tenants;
		}

		/**
		 * Add deals
		 * @param Deal $deals
		 * @return User
		 */
		public function addDeal(Deal $deals) {
			$this->deals[] = $deals;

			return $this;
		}

		/**
		 * Remove deals
		 * @param Deal $deals
		 */
		public function removeDeal(Deal $deals) {
			$this->deals->removeElement($deals);
			$deals->removeUser($this);
		}

		/**
		 * Get deals
		 * @return Deal[]|ArrayCollection
		 */
		public function getDeals() {
			$a = new ArrayCollection();
			foreach($this->getAllDeals() as $row) {
				if($row->getStatus() == Deal::STATUS_OPEN) {
					$a[] = $row;
				}
			}
			return $a;
		}

		/**
		 * Add history
		 * @param History $history
		 * @return User
		 */
		public function addHistory(History $history) {
			$this->history[] = $history;
			return $this;
		}

		/**
		 * Remove history
		 * @param History $history
		 */
		public function removeHistory(History $history) {
			$this->history->removeElement($history);
		}

		/**
		 * Get history
		 * @return ArrayCollection|History[]
		 */
		public function getHistory() {
			return $this->history;
		}

		/**
		 * Return all deals, internal lookup function to take advantage of type hinting
		 * @return Deal[]|ArrayCollection
		 */
		private function getAllDeals() {
			return $this->deals;
		}

		/**
		 * Add action
		 * @param Action $action
		 * @return User
		 */
		public function addAction(Action $action) {
			$this->action[] = $action;

			return $this;
		}

		/**
		 * Remove action
		 * @param Action $action
		 */
		public function removeAction(Action $action) {
			$this->action->removeElement($action);
		}

		/**
		 * Get action
		 * @return ArrayCollection|Queue[]
		 */
		public function getAction() {
			return $this->action;
		}


		/**
		 * getOffer
		 * @return Offer[]|ArrayCollection
		 */
		public function getOffer() {
			$a = new ArrayCollection();
			foreach($this->getInternalOffers() as $row) {
				if($row->getType() == Offer::TYPE_OFFER) {
					$a[] = $row;
				}
			}
			return $a;
		}

		/**
		 * Internal function to get offers, mainly for type hinting
		 * @return Offer[]|ArrayCollection
		 */
		private function getInternalOffers() {
			return $this->offer;
		}

		/**
		 * getAllOffers
		 * @return Offer[]|ArrayCollection
		 */
		public function getAllOffers() {
			$a = new ArrayCollection();

			foreach($this->getInternalOffers() as $row) {
				if($row->getType() == Offer::TYPE_OFFER) {
					$a[] = $row->getCustomer();
				}
			}
			return $a;
		}


		/**
		 * Get customers
		 * @return Customer[]|ArrayCollection
		 */
		public function getCustomers() {
			$a = new ArrayCollection();
			foreach($this->getInternalOffers() as $row) {
				if($row->getType() == Offer::TYPE_ACCEPTED) {
					$a[] = $row;
				}
			}
			return $a;
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
		 * Add sendtoqueues
		 * @param Queue $sendtoqueues
		 * @return User
		 */
		public function addSendtoqueue(Queue $sendtoqueues) {
			if(!$sendtoqueues) {
				return null;
			}
			$this->sendtoqueues[] = $sendtoqueues;
			$sendtoqueues->addSendtoUser($this);

			return $this;
		}

		/**
		 * Remove sendtoqueues
		 * @param Queue $sendtoqueues
		 */
		public function removeSendtoqueue(Queue $sendtoqueues) {
			if(!$sendtoqueues) {
				return;
			}
			$this->sendtoqueues->removeElement($sendtoqueues);
			$sendtoqueues->removeSendtoUser($this);
		}

		/**
		 * Get sendtoqueues
		 * @return ArrayCollection|Queue[]
		 */
		public function getSendtoqueues() {
			return $this->sendtoqueues;
		}

		/**
		 * Add queues1
		 * @param Queue $queues1
		 * @return User
		 */
		public function addQueues1(Queue $queues1) {
			$this->queues1[] = $queues1;
			$queues1->addUsers1($this);

			return $this;
		}

		/**
		 * Remove queues1
		 * @param Queue $queues1
		 */
		public function removeQueues1(Queue $queues1) {
			$this->queues1->removeElement($queues1);
			$queues1->removeUsers1($this);
		}

		/**
		 * Get queues1
		 * @return ArrayCollection|Queue[]
		 */
		public function getQueues1() {
			return $this->queues1;
		}

		/**
		 * Add queues2
		 * @param Queue $queues2
		 * @return User
		 */
		public function addQueues2(Queue $queues2) {
			$this->queues2[] = $queues2;
			$queues2->addUsers2($this);
			return $this;
		}

		/**
		 * Remove queues2
		 * @param Queue $queues2
		 */
		public function removeQueues2(Queue $queues2) {
			$this->queues2->removeElement($queues2);
			$queues2->removeUsers2($this);
		}

		/**
		 * Get queues2
		 * @return ArrayCollection|Queue[]
		 */
		public function getQueues2() {
			return $this->queues2;
		}

		/**
		 * Add queues3
		 * @param Queue $queues3
		 * @return User
		 */
		public function addQueues3(Queue $queues3) {
			$this->queues3[] = $queues3;
			$queues3->addUsers3($this);
			return $this;
		}

		/**
		 * Remove queues3
		 * @param Queue $queues3
		 */
		public function removeQueues3(Queue $queues3) {
			$this->queues3->removeElement($queues3);
			$queues3->removeUsers3($this);
		}

		/**
		 * Get queues3
		 * @return ArrayCollection|Queue[]
		 */
		public function getQueues3() {
			return $this->queues3;
		}

		/**
		 * Add queues4
		 * @param Queue $queues4
		 * @return User
		 */
		public function addQueues4(Queue $queues4) {
			$this->queues4[] = $queues4;
			$queues4->addUsers4($this);
			return $this;
		}

		/**
		 * Remove queues4
		 * @param Queue $queues4
		 */
		public function removeQueues4(Queue $queues4) {
			$this->queues4->removeElement($queues4);
			$queues4->removeUsers4($this);
		}

		/**
		 * Get queues4
		 * @return ArrayCollection|Queue[]
		 */
		public function getQueues4() {
			return $this->queues4;
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

//
//    /**
//     * Set tfkey
//     *
//     * @param string $tfkey
//     * @return User
//     */
//    public function setTfkey($tfkey)
//    {
//        $this->tfkey = $tfkey;
//
//        return $this;
//    }
//
//    /**
//     * Get tfkey
//     *
//     * @return string
//     */
//    public function getTfkey()
//    {
//        return $this->tfkey;
//    }

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

		/**
		 * Set appstatus
		 * @param boolean $appstatus
		 * @return User
		 */
		public function setAppstatus($appstatus) {
			$this->appstatus = $appstatus;

			return $this;
		}

		/**
		 * Get appstatus
		 * @return boolean
		 */
		public function getAppstatus() {
			return $this->appstatus;
		}
	}
