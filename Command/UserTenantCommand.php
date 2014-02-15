<?php

	namespace Vivait\AuthBundle\Command;

	use Vivait\AuthBundle\Entity\User;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class UserTenantCommand extends ContainerAwareCommand {

		protected function configure() {
			$this
				->setName('auth:user:tenants')
				->setDescription('Add/Remove a user from a tenant')
				->addArgument('tenant', InputArgument::REQUIRED, 'Code of the Tenant')
				->addArgument('action', InputArgument::REQUIRED, '[Add|Remove]')
				->addArgument('username', InputArgument::REQUIRED, 'Username of the User?');
		}


		protected function execute(InputInterface $input, OutputInterface $output) {
			$tenantcode = $input->getArgument('tenant');
			$action     = $input->getArgument('action');
			$username   = $input->getArgument('username');

			#check modifer
			if(!strtolower($action) == "add" && !strtolower($action) == "remove") {
				$output->writeln(sprintf("Error: Invalid action: %s, should be [Add|Remove]", $action));
				return;
			}

			#find tenant
			$tenant = $this->getContainer()->get('doctrine')
			               ->getRepository('VivaitAuthBundle:Tenant')
			               ->findOneBy(array('code' => $tenantcode));

			if(!$tenant) {
				$output->writeln(sprintf("Error: Could not find tenant with code: %s", $tenantcode));
				return null;
			}

			#find user
			$user = $this->getContainer()->get('doctrine')
			             ->getRepository('VivaitAuthBundle:User')
			             ->findOneBy(array('username' => $username));

			if(!$user) {
				$output->writeln(sprintf("Error: Could not find user with username: %s", $username));
				return null;
			}

			#should have now valid parameters
			if(strtolower($action) == 'add') {
				#check to see if already in tenant list
				if($user->getTenants()->contains($tenant)) {
					$output->writeln(sprintf("Error: %s is already associated with tenant: %s", $user->getFullname(),$tenant->getTenant()));
					return null;
				}
				$user->addTenant($tenant);
			} else {
				#check to see if already removed from tenant list
				if(!$user->getTenants()->contains($tenant)) {
					$output->writeln(sprintf("Error: %s not currently associated with tenant: %s", $user->getFullname(),$tenant->getTenant()));
					return null;
				}
				$user->removeTenant($tenant);
			}

			$em = $this->getContainer()->get('doctrine')->getManager();
			$em->persist($user);
			$em->flush();

			$output->writeln(sprintf("Success: %s has been %s %s %s",
			                         $user->getFullname(),
			                         strtolower($action) == 'add' ? 'associated' : 'removed',
			                         strtolower($action) == 'add' ? 'to' : 'from',
			                         $tenant->getTenant()
			                 ));
		}
	}