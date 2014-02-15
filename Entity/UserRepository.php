<?php
namespace Vivait\AuthBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class UserRepository extends EntityRepository implements UserProviderInterface {
	public function loadUserByUsername($username) {
		$q = $this
			->createQueryBuilder('u')
			->select('u, g, t')
			->leftJoin('u.groups', 'g')
			->leftJoin('u.tenants', 't')
			->where('(LOWER(u.username) = :username OR LOWER(u.email) = :email)')
			->setParameter('username', $username)
			->setParameter('email', $username)
			->getQuery();
		try {
			// The Query::getSingleResult() method throws an exception
			// if there is no record matching the criteria.
			$user = $q->getSingleResult();
		} catch (NoResultException $e) {
			$message = sprintf(
				'Unable to find an active user object identified by "%s".',
				$username
			);
			throw new UsernameNotFoundException($message, 0, $e);
		}

		return $user;
	}

	public function findAllFull() {
		return $this
			->createQueryBuilder('u')
			->select('u, g, t, q1, q2, q3, q4')
			->leftJoin('u.groups', 'g')
			->leftJoin('u.tenants', 't')
			->leftJoin('u.queues1', 'q1')
			->leftJoin('u.queues2', 'q2')
			->leftJoin('u.queues3', 'q3')
			->leftJoin('u.queues4', 'q4')
			->getQuery();
	}

	public function refreshUser(UserInterface $user) {
		$class = get_class($user);
		if (!$this->supportsClass($class)) {
			throw new UnsupportedUserException(
				sprintf(
					'Instances of "%s" are not supported.',
					$class
				)
			);
		}

		return $this->find($user->getId());
	}

	public function supportsClass($class) {
		return $this->getEntityName() === $class
					 || is_subclass_of($class, $this->getEntityName());
	}
}

?>