<?php

namespace Vivait\AuthBundle\Features\Context;

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Vivait\AuthBundle\Entity\Tenant;
use Vivait\AuthBundle\Entity\User;
use Vivait\AuthBundle\Entity\UserRepository;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Behat\Context\Step;
use Vivait\BehatAliceLoader\AliceContext;

class AuthContext extends BehatContext {
	use KernelDictionary;

	protected $current_user;
	protected $users = array();

	/**
	 * Gets current_user
	 * @return mixed
	 */
	public function getCurrentUser() {
		return $this->current_user;
	}

	/**
	 * Gets users
	 * @return array
	 */
	public function getUsers() {
		return $this->users;
	}

	/**
	 * Sets users
	 *
	 * @param array $users
	 *
	 * @return $this
	 */
	public function setUsers( $users ) {
		$this->users = $users;

		return $this;
	}

	/**
	 * @return ObjectManager|object
	 */
	protected function getManager()
	{
		$container = $this->getContainer();
		$em        = $container->get('doctrine')->getManager();
		return $em;
	}

	/**
	 * @param $username
	 */
	protected function getUserPassword($username) {
		if (!isset($this->users[$username]['password'])) {
			throw new \OutOfBoundsException('Invalid user '. $username);
		}

		return $this->users[ $username ]['password'];
	}

	/**
	 * @Given /^I am authenticated as "([^"]*)"$/
	 * @Given /^I am authenticated as "([^"]*)" using "([^"]*)"$/
	 */
	public function iAmAuthenticatedAs($username, $password = null) {
		if ($password === null) {
			$password = $this->getUserPassword($username);
		}

		$container = $this->getContainer();
		$em = $container->get('doctrine')->getManager();
		$this->current_user = $em
			->getRepository('VivaitAuthBundle:User')
			->findOneBy([
				'username' => $username
			]);

		return array(
			new Step\Given('I am on "/login"'),
			new Step\When('I fill in "_username" with "'. $username .'"'),
			new Step\When('I fill in "_password" with "'. $password .'"'),
			new Step\When('I press "Sign in"'),
			new Step\Then('I should not see "Login Failed"'),
		);
	}

	/**
	 * @Given /^there are the following users:$/
	 */
	public function thereAreTheFollowingUsers(TableNode $table) {
		foreach ($table->getHash() as $row) {
			$this->users[$row['username']] = $row;
		}

		/* @var $alice AliceContext */
		$alice = $this->getMainContext()->getSubcontextByClassName('Vivait\BehatAliceLoader\AliceContext');

		if ($alice) {
			/* @var $users User[] */
			$users = $alice->thereAreTheFollowing('VivaitAuthBundle:User', $table);

			foreach ($users as $user) {

				if (!$user->getTenants()->count()) {
					$tenant = $this->getManager()->getRepository('VivaitAuthBundle:Tenant')->findDefaultTenant();
					$tenant->addUser( $user );
					$this->getManager()->persist( $tenant );
				}

				$this->getManager()->persist( $user );
			}

			$this->getManager()->flush();
		}
	}

	/**
	 * @return UserRepository
	 */
	protected function getUserRepository()
	{
		$em = $this->getManager();
		return $em->getRepository('VivaitAuthBundle:User');
	}

	/**
	 * @return EntityRepository
	 */
	protected function getGroupRepository()
	{
		$em = $this->getManager();
		return $em->getRepository('VivaitAuthBundle:Group');
	}

	/**
	 * @return EntityRepository
	 */
	protected function getTenantRepository()
	{
		$em = $this->getManager();
		return $em->getRepository('VivaitAuthBundle:Tenant');
	}

} 