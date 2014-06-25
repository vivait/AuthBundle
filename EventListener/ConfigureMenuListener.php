<?php

namespace Vivait\AuthBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContext;
use Vivait\BootstrapBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener {
	/**
	 * @var SecurityContext
	 */
	private $security_context;

	/**
	 * @var int
	 */
	private $license_warning;

	function __construct(SecurityContext $security_context, $license_warning = 7) {
		$this->security_context = $security_context;
		$this->license_warning  = intval($license_warning);
	}

	/**
	 * @param ConfigureMenuEvent $event
	 */
	public function onMenuConfigure(ConfigureMenuEvent $event) {
		$menu  = $event->getMenu();
		$token = $this->security_context->getToken();

		$root = $menu->addChild('Auth Menu', [
			'navbar' => true,
		]);

		if ($token === null) {
			return;
		}

		/* @var $user \Vivait\AuthBundle\Entity\User */
		$user = $token->getUser();

		if ($user) {
			if (true || $user->getLicensedUntil() > new \DateTime('+' . $this->license_warning . ' days')) {
				$licensing = $root->addChild('Licensing Warning', [
					'dropdown' => true,
					'icon'     => 'warning-sign',
					'label'    => '',
					'pull-right' => true
				]);

				$licensing->setAttribute('class', $licensing->getAttribute('class') .' menu-danger');

				$licensing->addChild('This product will expire soon', [
					'dropdown-header' => true
				]);
				$licensing->addChild('Licensed until '. $user->getLicensedUntil()->format('d M Y H:i'), [
					'dropdown-header' => true
				]);
			}

//			$menu->addChild('Tenants', array('route' => 'vivait_auth_tenant'));
//			$menu->addChild('Groups', array('route' => 'vivait_auth_group'));
//			$menu->addChild('Users', array('route' => 'vivait_auth_user'));
		}
	}
}