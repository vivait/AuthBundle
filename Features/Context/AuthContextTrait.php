<?php

namespace Vivait\AuthBundle\Features\Context;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Vivait\AuthBundle\Entity\UserRepository;
use Behat\MinkExtension\Context\MinkContext;

/**
 * @mixin MinkContext
 */
trait AuthContextTrait  {
	public $current_user;

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
	 * @Given /^I am authenticated as "([^"]*)"$/
	 */
	public function iAmAuthenticatedAs($username, $password = 'password') {
		$this->visit('/login');
		$this->fillField('_username', $username);
		$this->fillField('_password', $password);
		$this->pressButton('Sign in');

		$container = $this->getContainer();
		$em = $container->get('doctrine')->getManager();
		$this->current_user = $em
			->getRepository('VivaitAuthBundle:User')
			->findOneBy([
				'username' => $username
			]);

		# @todo this needs fixing
		$this->assertPageNotContainsText('Login Failed');
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