<?php

namespace Vivait\AuthBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 * @ORM\Table(name="Auth_Users")
 * @ORM\Entity(repositoryClass="Vivait\AuthBundle\Entity\UserRepository")
 */
trait UserTrait {
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
}
