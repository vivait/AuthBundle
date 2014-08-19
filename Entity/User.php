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
 * @ORM\Table(name="Users")
 * @ORM\Entity(repositoryClass="Vivait\AuthBundle\Entity\UserRepository")
 */
class User extends BaseUser implements AdvancedUserInterface, \Serializable, \JsonSerializable, FootprintUserInterface {

	public function jsonSerialize() {
		return array(
			'id'       => $this->getId(),
			'initials' => $this->getInitials(),
			'fullname' => $this->getFullname(),
			'username' => $this->getUsername(),
			'gravatar' => $this->getGravatar(),
		);
	}

	public function __toString() {
		return $this->fullname;
	}

	/**
	 * @ORM\Column(type="string", length=60)
	 * @Assert\Length(min = "3", max="60");
	 * @Assert\Type(type="string")
     * @Serializer\Groups({"basic"})
	 */
	private $fullname;

	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "2", max="10");
	 * @Assert\Type(type="string")
     * @Serializer\Groups({"basic"})
	 */
	private $initials;

	/**
	 * @ORM\Column(type="string", length=60, nullable=true)
	 * @Assert\Length(max="60");
	 * @Assert\Type(type="string")
     * @Serializer\Groups({"basic"})
	 */
	private $jobtitle;

	/**
	 * @ORM\Column(type="string", length=60, nullable=true)
	 * @Assert\Length(max="60");
	 * @Assert\Type(type="string")
     * @Serializer\Groups({"basic"})
	 */
	private $department;

	/**
	 * @ORM\Column(type="string", length=60, nullable=true)
	 * @Assert\Length(max="60");
	 * @Assert\Type(type="string")
     * @Serializer\Groups({"basic"})
	 */
	private $location;

	/**
	 * @ORM\Column(type="string", length=15, nullable=true)
	 * @Assert\Regex("^\+?[0-9]{11,15}*$")
     * @Serializer\Groups({"basic"})
	 */
	private $telephone;




	/**
	 * This is called once Doctrine has loaded the entity
	 * @ORM\PostLoad
	 */

#############################################



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

		if ($include_tenant && ($tenants = $this->getTenants()) && $tenants->count()) {

			foreach ($tenants as $row) {
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
}
