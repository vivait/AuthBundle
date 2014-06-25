<?php
namespace Vivait\AuthBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class TenantRepository extends EntityRepository {
	private $default_tenant;

	public function findDefaultTenant() {
		if (!$this->default_tenant) {
			$this->default_tenant = $this->findOneBy([
				'code' => 'DEF'
			]);
			if (!$this->default_tenant) {
				$this->default_tenant = new Tenant('DEF', 'Default tenant');
			}
		}


		return $this->default_tenant;
	}
}

?>
