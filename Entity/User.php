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
 * @ORM\Table(name="Users")
 * @ORM\Entity(repositoryClass="Vivait\AuthBundle\Entity\UserRepository")
 */
class User extends UserAbstract {
}