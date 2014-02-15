<?php

	namespace Vivait\AuthBundle\Command;

	use Vivait\AuthBundle\Entity\User;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class UserGroupCommand extends ContainerAwareCommand {

		protected function configure() {
			$this
				->setName('auth:user:roles')
				->setDescription('Add/Remove a user from a role')
				->addArgument('role', InputArgument::REQUIRED, 'Code of the role')
				->addArgument('action', InputArgument::REQUIRED, '[Add|Remove]')
				->addArgument('username', InputArgument::REQUIRED, 'Username of the User?');
		}


		protected function execute(InputInterface $input, OutputInterface $output) {
			$rolecode = $input->getArgument('role');
			$action     = $input->getArgument('action');
			$username   = $input->getArgument('username');

			#check modifer
			if(!strtolower($action) == "add" && !strtolower($action) == "remove") {
				$output->writeln(sprintf("Error: Invalid action: %s, should be [Add|Remove]", $action));
				return;
			}

			#find role
			$role = $this->getContainer()->get('doctrine')
			               ->getRepository('VivaitAuthBundle:Group')
			               ->findOneBy(array('role' => $rolecode));

			if(!$role) {
				$output->writeln(sprintf("Error: Could not find role with code: %s", $role));
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
				#check to see if already in role list
				if($user->getGroups()->contains($role)) {
					$output->writeln(sprintf("Error: %s is already associated with tenant: %s", $user->getFullname(),$role->getName()));
					return null;
				}
				$user->addGroup($role);
			} else {
				#check to see if already removed from role list
				if(!$user->getGroups()->contains($role)) {
					$output->writeln(sprintf("Error: %s not currently associated with tenant: %s", $user->getFullname(),$role->getName()));
					return null;
				}
				$user->removeGroup($role);
			}

			$em = $this->getContainer()->get('doctrine')->getManager();
			$em->persist($user);
			$em->flush();

			$output->writeln(sprintf("Success: %s has been %s %s role %s",
			                         $user->getFullname(),
			                         strtolower($action) == 'add' ? 'added' : 'removed',
			                         strtolower($action) == 'add' ? 'to' : 'from',
			                         $role->getName()
			                 ));
		}
	}