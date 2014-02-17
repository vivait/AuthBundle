<?php
namespace Vivait\AuthBundle\EventListener;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Vivait\AuthBundle\Entity\Tenant;

class TenantManager implements EventSubscriberInterface
{
	const SESSION_VAR = 'vivait_auth_tenant';

	/* @var $security_context SecurityContext */
	protected $security_context;

	/* @var $tenant Tenant */
	protected $tenant;

	function __construct(SecurityContext $security_context, Logger $logger, EntityRepository $tenant_repository) {
		$this->security_context  = $security_context;
		$this->logger            = $logger;
		$this->tenant_repository = $tenant_repository;
	}


	public function onKernelRequest(GetResponseEvent $event)
	{
		$request   = $event->getRequest();
		$token     = $this->security_context->getToken();
		if (!$token || !is_object($token)) {
			return;
		}

		/* @var $user User */
		$user      = $token->getUser();
		$is_object = $user instanceOf User;

		// Try the request first
		$tenant = $this->getRequestTenant($request);

		// Then look at the user's highest priority setting
		if ($is_object && !$tenant) {
			$tenant = $this->getUserTenant();
		}

		// Now just pick the global highest priority
		if (!$tenant) {
			$tenant = $this->getDefaultTenant();
		}

		// Did we find a tenant?
		if ($tenant) {
			// Store them against the request
			$this->setTenant($tenant);

			// Store it against the user
			if ($is_object) {
				$user->setCurrentTenant($tenant);
			}
		}
		else {
			// TODO: Throw a big fat warning
			$this->logger->warning('No default tenant could be found');
		}
	}

	protected function getDefaultTenant() {
		return $this->tenant_repository->findOneBy(array(), array(
			'priority' => 'ASC'
		));
	}

	protected function getRequestTenant(Request $request) {
		$session   = $request->getSession();
		$tenant_id = $request->get('_tenant');
		$tenant_changed = true;

		if (!$tenant_id) {
			$tenant_id = $session->get(self::SESSION_VAR);
			$tenant_changed = false;
		}

		if ($tenant_id) {
			$tenant = $this->tenant_repository->find($tenant_id);

			// Tenant not in database
			if (!$tenant) {
				$this->logger->warning(sprintf('Tenant ID "%s" could not be loaded by repository', $tenant_id));
			}


			$session->set(self::SESSION_VAR, $tenant_id);

			return $tenant;
		}
		return null;
	}

	protected function getUserTenant() {
		$token = $this->security_context->getToken();
		/* @var $user User */
		$user = $token->getUser();

		if ($user instanceOf User) {
			return $user->getTenants()->first();
		}
		return null;
	}

	public static function getSubscribedEvents()
	{
		return array(
			// must be registered before the default Locale listener
			KernelEvents::REQUEST => array(array('onKernelRequest', 1)),
		);
	}

	/**
	 * Sets tenant
	 * @param mixed $tenant
	 * @return $this
	 */
	public function setTenant(Tenant $tenant) {
		$this->tenant = $tenant;
		return $this;
	}

	/**
	 * Gets tenant
	 * @return Tenant
	 */
	public function getTenant() {
		return $this->tenant;
	}
}